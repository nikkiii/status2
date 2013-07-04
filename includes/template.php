<?php 
require_once "smarty/Smarty.class.php";

//TODO admin panel variable
$theme = "bootstrap";

$smarty = new Smarty();
$smarty->setConfigDir("templates/$theme/configs/");
$smarty->setTemplateDir("templates/$theme/source/");
$smarty->setCompileDir("templates/$theme/compiled/");

if(file_exists('templates/' . $theme . '/theme.php')) {
	require_once 'templates/' . $theme . '/theme.php';
}

$smarty->assignGlobal("title", "Server Status");
$smarty->assignGlobal("version", STATUS_VERSION);
?>