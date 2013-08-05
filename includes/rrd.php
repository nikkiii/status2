<?php
// Since RRDTool-PHP will segfault if we misconfigure an archive, we can use this to make sure that doesn't happen and we get a nice error.
require_once 'phprrd.php';
require_once 'functions.php';

define('IFACE_FILE', 'network_%s.rrd');
define('DISK_FILE', 'disk_%s.rrd');

/**
 * Status v2 RRD Class
 * 
 * Provides methods to create databases and update values
 * 
 * @author Nikki
 *
 */
class StatusRRD {
	
	var $dir;
	var $step;
	
	public function __construct($id, $step = 300) {
		$this->dir = dirname(dirname(__FILE__)) . '/rrd/' . $id . '/';
		if(!is_dir($this->dir)) {
			mkdir($this->dir, 0770, true);
		}
		$this->step = $step;
		$this->heartbeat = ($step * 1.3);
	}
	
	// Creation of rrd files
	
	public function createMemory() {
		$creator = new PHPRRDCreator($this->buildpath('memory.rrd'), time(), $this->step);
		$creator->addDataStoreEx('total', DS_GAUGE, $this->heartbeat);
		$creator->addDataStoreEx('used', DS_GAUGE, $this->heartbeat);
		$creator->addDataStoreEx('free', DS_GAUGE, $this->heartbeat);
		$creator->addDataStoreEx('bufcac', DS_GAUGE, $this->heartbeat);
		// Archives
		$this->addAverages($creator);
		// Save
		$creator->save();
	}
	
	public function createNetwork($path) {
		$creator = new PHPRRDCreator($path, time(), $this->step);
		$creator->addDataStoreEx('in', DS_COUNTER, $this->heartbeat);
		$creator->addDataStoreEx('out', DS_COUNTER, $this->heartbeat);
		// Archives
		$this->addAverages($creator);
		// Save
		$creator->save();
	}
	
	public function createDisk($path) {
		$creator = new PHPRRDCreator($path, time(), $this->step);
		$creator->addDataStoreEx('total', DS_GAUGE, $this->heartbeat);
		$creator->addDataStoreEx('used', DS_GAUGE, $this->heartbeat);
		$creator->addDataStoreEx('free', DS_GAUGE, $this->heartbeat);
		// Archives
		$this->addAverages($creator);
		// Save
		$creator->save();
	}
	
	public function createLoad($path) {
		$creator = new PHPRRDCreator($path, time(), $this->step);
		$creator->addDataStoreEx('load1', DS_GAUGE, $this->heartbeat);
		$creator->addDataStoreEx('load5', DS_GAUGE, $this->heartbeat);
		$creator->addDataStoreEx('load15', DS_GAUGE, $this->heartbeat);
		// Archives
		$this->addAverages($creator);
		// Save
		$creator->save();
	}
	
	private function addAverages($creator) {
		$creator->addArchiveEx(RRA_AVERAGE, 0.5, 1, 168); // 1 hour, keep up to a week of this accuracy
		$creator->addArchiveEx(RRA_AVERAGE, 0.5, 1440, 93); // 1 day, keep up to a 3 months of this accuracy
		$creator->addArchiveEx(RRA_AVERAGE, 0.5, 10080, 52); // 1 week, keep up to a year of this accuracy.
		$creator->addArchiveEx(RRA_AVERAGE, 0.5, 525600, 120); // 1 month, keep up to 10 years of this accuracy
		$creator->addArchiveEx(RRA_AVERAGE, 0.5, 191844000, 4096); // 1 year, keep up to 4096 years of this accuracy
		
		$creator->addArchiveEx(RRA_MAX, 0.5, 1, 168); // 1 hour, keep up to a week of this accuracy
		$creator->addArchiveEx(RRA_MAX, 0.5, 1440, 93); // 1 day, keep up to a 3 months of this accuracy
		$creator->addArchiveEx(RRA_MAX, 0.5, 10080, 52); // 1 week, keep up to a year of this accuracy.
		$creator->addArchiveEx(RRA_MAX, 0.5, 525600, 120); // 1 month, keep up to 10 years of this accuracy
		$creator->addArchiveEx(RRA_MAX, 0.5, 191844000, 4096); // 1 year, keep up to 4096 years of this accuracy
	}
	
	// Updating
	
	/**
	 * Update the rrd files with the data supplied
	 * This function will verify the data exists.
	 * 
	 * @param Object $data	The data parsed from JSON
	 */
	public function update($data) {
		global $config;
		// Memory update
		if(isset($data->ram))
			$this->_memoryUpdate($data->ram);
		// Disk update
		if(isset($data->disk)) {
			$this->_diskUpdate('total', $data->disk->total);
			foreach($data->disk->single as $val) {
				$fs = $val->fs;
				if(!accept_fs($fs)) {
					continue;
				}
				if($fs[0] == '/') {
					$fs = substr($fs, strrpos($fs, '/')+1);
				}
				$this->_diskUpdate($fs, $val);
			}
		}
		// Load update
		if(isset($data->uplo)) {
			$this->_loadUpdate($data->uplo);
		}
		// Interfaces
		if(isset($data->interfaces)) {
			foreach($data->interfaces as $iface => $val) {
				$this->_netUpdate($iface, $data->interfaces->$iface);
			}
		}
	}
	
	private function _memoryUpdate($data) {
		$file = $this->buildpath('memory.rrd');
		if(!file_exists($file)) {
			$this->createMemory($file);
		}
		$u = new RRDUpdater($file);
		$u->update(array('total' => $data->total, 'used' => $data->used, 'free' => $data->free, 'bufcac' => $data->bufcac));
	}
	
	private function _diskUpdate($dev, $data) {
		$file = $this->buildpath(sprintf(DISK_FILE, $dev));
		if(!file_exists($file)) {
			$this->createDisk($file);
		}
		$u = new RRDUpdater($file);
		$u->update(array('total' => $data->total, 'used' => $data->used, 'free' => $data->avail));
	}
	
	private function _loadUpdate($loads) {
		$file = $this->buildpath('loads.rrd');
		if(!file_exists($file)) {
			$this->createLoad($file);
		}
		$u = new RRDUpdater($file);
		$u->update(array('load1' => $loads->load1, 'load5' => $loads->load5, 'load15' => $loads->load15));
	}
	
	private function _netUpdate($iface, $data) {
		$f = $this->buildpath(sprintf(IFACE_FILE, $iface));
		if(!file_exists($f)) {
			$this->createNetwork($f);
		}
		$u = new RRDUpdater($f);
		$u->update(array('in' => $data[0], 'out' => $data[8]));
	}
	
	/**
	 * Get the 'heartbeat' time, we'll use step + 1/3 of step
	 */
	private function getHeartbeat() {
		return $this->step * 1.3;
	}
	
	private function buildpath($file) {
		return $this->dir . $file;
	}
	
	private function _base() {
		return array( '--start', time(), '--step', $this->step);
	}
}