<?php
require_once "includes/config.php";
require_once "includes/template.php";
require_once "includes/database.php";
require_once "includes/functions.php";

$dbs = $db->prepare('SELECT servers.name,servers.provider,stats.* FROM servers, stats WHERE stats.serverid = servers.id AND stats.id = (SELECT id FROM stats WHERE serverid = servers.id ORDER BY time DESC LIMIT 1)');
$dbs->execute();

$providerq = $db->prepare('SELECT id, shortname, name FROM providers');
$providerq->execute();

$providers = array();

foreach($providerq->fetchAll(PDO::FETCH_OBJ) as $provider) {
	$provider->servers = array();
	$providers[$provider->id] = $provider;
}

uasort($providers, 'sort_providers');

$servers = $dbs->fetchAll(PDO::FETCH_OBJ);

$extraq = $db->prepare('SELECT time,interfaces FROM stats WHERE serverid = ? ORDER BY time DESC LIMIT 1, 1');

foreach($servers as $server) {
	if(!empty($config['display']['nobuffers'])) {
		$server->memused -= $server->membuffers;
	}
	if(!empty($server->interfaces)) {
		$json = json_decode($server->interfaces);
	
		// TODO add in an 'interfaces' field on the servers table? And of course optimize this.
		$keys = array_keys(get_object_vars($json));
		$idata = $json->$keys[0];
		
		$extraq->execute(array($server->serverid));
		
		$r = $extraq->fetch(PDO::FETCH_OBJ);
		
		$json2 = json_decode($r->interfaces);
		
		$idata2 = $json2->$keys[0];
		
		//Idx 0 = rx, 8 = tx
		$server->netin = humansize(intval(bcdiv(bcsub($idata[0], $idata2[0]), $server->time - $r->time)));
		$server->netout = humansize(intval(bcdiv(bcsub($idata[8], $idata2[8]), $server->time - $r->time)));
	}
	$providers[$server->provider]->servers[] = $server;
}

$tpl = $smarty->createTemplate("index.tpl");
$tpl->assign('scripts', array('js/status.js'));
$tpl->assign('stylesheets', array('css/status.css'));
$tpl->assign('providers', $providers);
$tpl->display();
?>