<?php
require_once "includes/config.php";
require_once "includes/database.php";

$result = file_get_contents("php://input");
$result = json_decode($result, true);

$query = $db->prepare("SELECT COUNT(*) AS count FROM servers WHERE id = ? AND passkey = ?");
$query->execute(array($result['uid'], sha1($result['key'])));
$res = $query->fetch(PDO::FETCH_ASSOC);
if($res['count'] == 0) {
	die("unauthorized");
}

$fields = array(
	'serverid',
	'time',
	'uptime',
	'status',
	'memtotal', 'memused', 'memfree', 'membuffers',
	'disktotal', 'diskused', 'diskfree',
	'load1', 'load5', 'load15',
	'interfaces',
	'processes'
);

$quests = str_repeat("?,", count($fields));
$quests = rtrim($quests, ",");

$dbq = $db->prepare("INSERT INTO stats (`" . implode("`,`", $fields) . "`) VALUES($quests)");

$data = array(
	//Server ID
	intval($result['uid']),
	//Time
	time(),
	//Uptime
	$result['uplo']['uptime'],
	//Status
	true,
	//Memory
	$result['ram']['total'], 
	$result['ram']['used'], 
	$result['ram']['free'], 
	$result['ram']['bufcac'], 
	//Disk
	$result['disk']['total']['total'], 
	$result['disk']['total']['used'], 
	$result['disk']['total']['avail'],
	//Separate filesystems
	$result['disk']['single'],
	//Loads
	$result['uplo']['load1'], 
	$result['uplo']['load5'], 
	$result['uplo']['load15'],
	//Interfaces
	isset($result['interfaces']) ? json_encode($result['interfaces']) : '',
	//Processes
	json_encode($result['ps'])
);

$dbq->execute($data);

//Cleanup
$q = $db->prepare("DELETE FROM stats WHERE time <= ?");
$q->execute(array(time() - (3600 * 24 * 30)));
?>