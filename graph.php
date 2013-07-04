<?php
// This file is horribly unfinished.
require_once "includes/config.php";
require_once "includes/database.php";

if(empty($_GET['serverid'])) {
	die('Invalid server');
}
$serverq = $db->prepare("SELECT id,name,provider FROM servers WHERE id = ?");
$serverq->execute(array($_GET['serverid']));

$server = $serverq->fetch(PDO::FETCH_OBJ);

if(!$server) {
	die('Invalid server');
}

/* Library settings */
define("CLASS_PATH", "includes/pChart/class");
define("FONT_PATH", "includes/pChart/fonts");

/* pChart library inclusions */
include(CLASS_PATH."/pData.class.php");
include(CLASS_PATH."/pDraw.class.php");
include(CLASS_PATH."/pImage.class.php");

/* Create and populate the pData object */
$data = new pData();

$mintime = strtotime("-1 hour");

$scaleSettings = array(
	"XMargin" => 10,
	"YMargin" => 10,
	"Floating" => true,
	"GridR" => 200,
	"GridG" => 200,
	"GridB" => 200,
	"DrawSubTicks" => TRUE,
	"CycleBackground" => TRUE,
	"LabelSkip" => 9
);

$title = 'Unknown';

$times = array();
if(isset($_GET['iface'])) {
	$interface = $_GET['iface'];
	$title = 'Bandwidth on ' . $interface . ' (1 hour)';
	$dbs = $db->prepare('SELECT time,interfaces FROM stats WHERE serverid = ? AND time >= ? ORDER BY time DESC');
	$dbs->execute(array($server->id, $mintime));
	
	$stats = $dbs->fetchAll(PDO::FETCH_OBJ);
	$stats = array_reverse($stats);
	
	$json = json_decode($stats[0]->interfaces);
	
	if(!isset($json->$interface)) {
		die('Invalid interface');
	}
	
	$readings = array('rx' => array(), 'tx' => array());
	
	$min = false;
	$max = 0;
	$unit = 'Megabit';
	
	$idata = $json->$interface;
	$lastrx = $idata[0];
	$lasttx = $idata[8];
	
	$count = count($stats);
	for($i = 1; $i < $count; $i++) {
		$stat = $stats[$i];
		if(empty($stat->interfaces)) continue;
	
		$times[] = date('g:i a', $stat->time);
		$json = json_decode($stat->interfaces);
	
		$idata = $json->$interface;
		//Idx 0 = rx, 8 = tx
		$diffrx = bcsub($idata[0], $lastrx);
		$difftx = bcsub($idata[8], $lasttx);
		
		$lastrx = $idata[0];
		$lasttx = $idata[8];
		if($diffrx && $difftx) {
			$kilobitrx = bcdiv($diffrx, 1024);
			$kilobittx = bcdiv($difftx, 1024);
				
			$kilobitrx = ($kilobitrx / 60) * 8;
			$kilobittx = ($kilobittx / 60) * 8;
				
			if($kilobitrx < 1024 || $kilobittx < 1024) {
				$unit = 'Kilobit';
			}
			if($kilobitrx > $max) {
				$max = $kilobitrx;
			}
			if($kilobittx > $max) {
				$max = $kilobittx;
			}
			if(!$min || $kilobitrx < $min) {
				$min  = $kilobitrx;
			}
			if(!$min || $kilobittx < $min) {
				$min  = $kilobittx;
			}
			$readings['rx'][] = $kilobitrx;
			$readings['tx'][] = $kilobittx;
		}
	}
	
	if($unit == 'Megabit' || $max > 10000) {
		$unit = 'Megabit';
		$max = 0;
		foreach($readings as $key => &$values) {
			foreach($values as &$value) {
				$value = $value / 1024;
				if($value < $min) {
					$min  = $value;
				} else if($value > $max) {
					$max = $value;
				}
			}
		}
	}
	
	if($min < 0) $min = 0;
	
	$scaleSettings['Mode'] = SCALE_MODE_MANUAL;
	$scaleSettings['ManualScale'] = array(
		0 => array("Min" => $min > 0 ? $min - 1 : $min,"Max" => $max)
	);
	
	$data->addPoints($readings['rx'],"RX");
	$data->addPoints($readings['tx'],"TX");
	$data->setAxisName(0, "$unit/s");
} else if(isset($_GET['memory'])) {
	$title = 'Memory usage (1 hour)';
	$dbs = $db->prepare('SELECT time,memtotal,memused,membuffers FROM stats WHERE serverid = ? AND time >= ? ORDER BY time DESC');
	$dbs->execute(array($server->id, $mintime));
	
	$stats = $dbs->fetchAll(PDO::FETCH_OBJ);
	$stats = array_reverse($stats);
	
	$total = $stats[0]->memtotal / 1024;
	$min = $stats[0]->memused - $stats[0]->membuffers;
	$readings = array();
	
	foreach($stats as $stat) {
		$times[] = date('g:i a', $stat->time);
		$used = $stat->memused - $stat->membuffers;
		$used = $used / 1024;
		if($used < $min) {
			$min = $used;
		}
		$readings[] = $used;
	}
	
	$scaleSettings['Mode'] = SCALE_MODE_MANUAL;
	$scaleSettings['ManualScale'] = array(
		0 => array("Min" => $min - 1,"Max" => $total)
	);
	
	$data->addPoints($readings, 'Memory Used');
	$data->setAxisName(0, "Gigabytes");
} else if(isset($_GET['disk'])) {
	$title = 'Disk usage (1 hour)';
	$dbs = $db->prepare('SELECT time,disktotal,diskused,diskfree FROM stats WHERE serverid = ? AND time >= ? ORDER BY time DESC');
	$dbs->execute(array($server->id, $mintime));
	
	$stats = $dbs->fetchAll(PDO::FETCH_OBJ);
	$stats = array_reverse($stats);
	
	$total = $stats[0]->disktotal;
	$min = $stats[0]->diskused;
	
	$readings = array();
	
	foreach($stats as $stat) {
		$times[] = date('g:i a', $stat->time);
		$used = $stat->diskused;
		if($used < $min) {
			$min = $used;
		}
		$readings[] = $used;
	}
	
	$scaleSettings['Mode'] = SCALE_MODE_MANUAL;
	$scaleSettings['ManualScale'] = array(
		0 => array("Min" => $min > 20 ? $min - 20 : 0,"Max" => $total)
	);
	
	$data->addPoints($readings, 'Disk Used');
	$data->setAxisName(0, "Gigabytes");
}

// Read data from the database, and add it
$data->addPoints($times, "Labels");
$data->setSerieDescription("Labels", "Time of day");
$data->setAbscissa("Labels");


$chart = new pImage(700,230,$data);

/* Turn of Antialiasing */
$chart->Antialias = FALSE;

/* Add a border to the picture */
$chart->drawRectangle(0,0,699,229,array(
	"R" => 0,
	"G" => 0,
	"B" => 0
));

/* Write the chart title */
$chart->setFontProperties(array("FontName"=>FONT_PATH . "/Forgotte.ttf","FontSize"=>11));
$chart->drawText(150, 35, $title, array(
	"FontSize" => 20,
	"Align" => TEXT_ALIGN_BOTTOMMIDDLE
));

/* Set the default font */
$chart->setFontProperties(array(
	"FontName" => FONT_PATH . "/pf_arma_five.ttf",
	"FontSize" => 6
));

/* Define the chart area */
$chart->setGraphArea(60,40,650,200);

/* Draw the scale */
$chart->drawScale($scaleSettings);

/* Write the chart legend */
$chart->drawLegend(540,20,array(
	"Style" => LEGEND_NOBORDER,
	"Mode" => LEGEND_HORIZONTAL
));

/* Turn on Antialiasing */
$chart->Antialias = TRUE;

/* Draw the area chart */
$chart->drawAreaChart();

/* Render the picture (choose the best way) */
$chart->stroke();
?>