<?php
/**
 * This file contains PHP implementations of the RRD PECL extension objects.
 * It was made because the extension will segfault when attempting to create a file without archives, and error messages are always good.
 */

define('DS_GAUGE', 'GAUGE');
define('DS_COUNTER', 'COUNTER');
define('DS_DERIVE', 'DERIVE');

define('RRA_AVERAGE', 'AVERAGE');
define('RRA_MAX', 'MAX');

/**
 * A PHP implementation of the PECL extension's RRDCreator class, which does not segfault when there are no archives or are other errors...
 * 
 * @author Nikki
 */
class PHPRRDCreator {
	private $path;
	private $start;
	private $step;
	private $error;
	
	private $datastores = array();
	private $archives = array();
	
	public function __construct($path, $start = 0, $step = 300) {
		$this->path = $path;
		$this->start = $start;
		$this->step = $step;
	}
	
	public function addDataStore($description) {
		$this->datastores[] = 'DS:' . $description;
	}
	
	public function addDataStoreEx($name, $type, $heartbeat, $min = 'U', $max = 'U') {
		$this->addDataStore(implode(':', array($name, $type, $heartbeat, $min, $max)));
	}
	
	public function addArchive($description) {
		$this->archives[] = 'RRA:' . $description;
	}
	
	public function addArchiveEx($cf, $xff, $step, $rows) {
		$this->addArchive(implode(':', array($cf, $xff, $step, $rows)));
	}
	
	public function save() {
		$args = array();
		if($this->start != 0) {
			$args[] = '--start';
			$args[] = $this->start;
		}
		if($this->step) {
			$args[] = '--step';
			$args[] = $this->step;
		}
		$args = array_merge($args, $this->datastores, $this->archives);
		
		$res = rrd_create($this->path, $args);
		if(!$res) {
			$this->error = rrd_error();
		}
		return $res;
	}
	
	public function error() {
		return $this->error;
	}
}

/**
 * This isn't used, but it's an untested implementation straight from the C converted to PHP
 * 
 * @author Nikki
 */
class PHPRRDUpdater {
	private $path;
	private $error;
	
	public function __construct($path) {
		$this->path = $path;
	}
	
	public function update($arr, $time = false) {
		// Mimics the internal methods of the extension
		$ds_names = '';
		$ds_vals = '';
		
		if(!$time) {
			$time = 'N';
		}
		
		$ds_count = count($arr);
		
		foreach($arr as $k => $v) {
			if(strlen($ds_names) > 0) {
				$ds_names .= ':';
			} else {
				$ds_names .= '--template=';
			}
			
			$ds_names .= $k;
			
			if(strlen($ds_vals) == 0) {
				$ds_vals .= $time;
			}
			
			$ds_vals .= ':';
			$ds_vals .= $v;
		}
		
		$ret = rrd_update($this->path, array($ds_names, $ds_vals));
		if(!$ret) {
			$this->error = rrd_error();
		}
		return $ret;
	}
	
	public function error() {
		return $this->error;
	}
}