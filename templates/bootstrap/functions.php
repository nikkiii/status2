<?php
function progressClass($percentage) {
	if($percentage >= 90) {
		return 'progress-danger';
	} else if($percentage >= 75) {
		return 'progress-warning';
	}
	return 'progress-success';
}
?>