<?php
//Define any functions here, this'll be loaded globally.

//Theme information
$themeinfo = array(
	'name' => 'Bootstrap',
	'author' => 'Nikki',
	'description' => 'Standard Bootstrap theme',
	'version' => "1.0"
);

//Any global functions
function progressClass($percentage) {
	if($percentage >= 90) {
		return 'progress-danger';
	} else if($percentage >= 75) {
		return 'progress-warning';
	}
	return 'progress-success';
}
?>