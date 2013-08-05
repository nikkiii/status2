<?php

function handle_controller($base, $arr) {
	$controller = load_controller($base, $arr[1]);
	
	$method = 'index';
	if(!empty($uri[2]))
		$method = preg_replace('/[^a-zA-Z0-9]+/', '', $uri[2]);
	
	if(method_exists($controller, $method)) {
		$controller->$method();
	}
}

function load_controller($base, $name) {
	$name = preg_replace('/[^a-zA-Z0-9]+/', '', $name);
	if(file_exists($base . '/' . $name . '.php')) {
		// Convert name to camel case (So, bla_bla would be BlaBla, bla would be Bla, etc.)
		$clname = preg_replace('/(?:^|_)(.?)/e', "strtoupper('$1')", $name);
		// Initialize a new input class
		$input = new Status_Input($_GET, $_POST);
		// Initialize the new controller
		$controller = false;
		if(class_exists($clname)) {
			$controller = new $clname($input, $pdo, $smarty);
		}
		return $controller;
	}
	return false;
}

/**
 * This class mimics CI's Controller, except it is used a bit differently (No models, views, etc.)
 * 
 * @author Nikki
 *
 */
class Status_Controller {
	var $input;
	var $db;
	var $smarty;
	
	public function __construct($input, $db, $smarty) {
		$this->input = $input;
		$this->db = $db;
		$this->smarty = smarty;
	}
}

class Status_Input {
	var $get;
	var $post;
	
	public function __construct($get, $post) {
		$this->get = $get;
		$this->post = $post;
	}
	
	public function get($key) {
		if(!isset($this->get[$key])) {
			return false;
		}
		return $this->get[$key];
	}
	
	public function post($key) {
		if(!isset($this->post[$key])) {
			return false;
		}
		return $this->post[$key];
	}
}
?>