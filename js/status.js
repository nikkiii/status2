$(document).ready(function() {
	$('a[data-toggle="tab"]').on('shown', function (e) {
		var $targ = $(e.target).attr('href').substring(1);
		if($targ == 'all'){ 
			$('div.tab-pane').addClass('active');
		}
	});
});