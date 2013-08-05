<?php
error_reporting(E_ALL);

define("STATUS_VERSION", "2.0.1");

$config = array(
	'display' => array(
		'nobuffers' => true
	),
	'accepted_fs' => array(
		'/dev/simfs'
	)
);

$config['database'] = array(
	'type' => 'mysql',
	'hostname' => 'localhost',
	'database' => 'servers',
	'username' => 'servers',
	'password' => 'password'
);
?>