<?php
require_once "includes/config.php";
require_once "includes/template.php";
require_once "includes/database.php";
require_once "includes/controller.php";

$uri = parse_uri();

// Pass it off to the controller methods
handle_controller(dirname(__FILE__) . '/admin', array_slice($uri, 1));