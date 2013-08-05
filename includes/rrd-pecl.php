<?php
// Provides outlines of class methods for Eclipse
function rrd_create(string $filename, array $options) {}
function rrd_update(string $filename, array $options) {}

class RRDCreator {
	public function __construct (string $path, string $startTime = '', int $step = 0) {}
	
	public function addArchive(string $description) {}
	public function addDataSource(string $description) {}
	
	public function save ( ) {}
}