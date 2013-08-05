<?php
// TODO turn this into a controller
$base = dirname(dirname(__FILE__));

$themes = array();

$dir = opendir($base . '/templates');
while($file = readdir($dir)) {
	$path = $base . '/templates/' . $file . '/theme.php';
	if(file_exists($path)) {
		require_once $path;
		
		$themes[$file] = $themeinfo;
	}
}
closedir($dir);

print_r($themes);