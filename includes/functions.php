<?php
function sec_human($sec) {
	if($sec < 60) { return $sec.'s'; }
	$tstring = '';
	$days  = floor($sec / 86400);
	$hrs   = floor(bcmod($sec, 86400) / 3600);
	$mins  = round(bcmod(bcmod($sec, 86400), 3600) / 60);
	if($days > 0) { $tstring = $days.'d '; }
	if($hrs  > 0) { $tstring .= $hrs.'h '; }
	if($mins > 0) { $tstring .= $mins.'m '; }
	return substr($tstring, 0, -1);
}
function sort_providers($p1, $p2) {
	return strcmp($p1->name, $p2->name);
}
function humansize($size) { 
    $mod = 1024;
    $units = explode(' ','B KB MB GB TB PB');
    for ($i = 0; $size > $mod; $i++) {
        $size /= $mod;
    }
    return round($size, 2) . ' ' . $units[$i];
}

/**
 * Check if a filesystem is a valid listable type
 * @param $fs
 */
function accept_fs($fs) {
	global $config;
	if(preg_match('/\/dev\/[hvs]d/', $fs))
		return true;
	
	return in_array($fs, $config['accepted_fs']);
}

function parse_uri($uri=false) {
	if(!$uri) {
		$uri = $_SERVER['REQUEST_URI'];
		if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
			$uri = substr($uri, strlen($_SERVER['SCRIPT_NAME']));
		} elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0) {
			$uri = substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
		}
	}
	$segments = array();
	foreach (explode("/", preg_replace("|/*(.+?)/*$|", "\\1", $uri)) as $val) {
		// Filter segments for security
		$val = trim($val);
		if($val != '') {
			$segments[] = $val;
		}
	}
	return $segments;
}
?>