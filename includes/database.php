<?php
if(empty($config['database'])) {
	die('Unable to connect to database, please configure the application.');
}
$database = $config['database'];
try {
	switch($database['type']) {
		case 'sqlite':
			$db = new PDO('sqlite:' . $database['file']);
			break;
		case 'mysql':
			$db = new PDO("mysql:host=$database[hostname];dbname=$database[database]", $database['username'], $database['password']);
			break;
		default:
			die('Database not configured!');
	}
} catch(PDOException $e) {
	die("Unable to connect to database");
}