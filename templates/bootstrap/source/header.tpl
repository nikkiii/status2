
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>{$title}</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">

		<!-- Le styles -->
		<link href="css/bootstrap.css" rel="stylesheet">
		<style>
			body {
				padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
			}
		</style>
		<link href="css/bootstrap-responsive.css" rel="stylesheet">
		{if isset($stylesheets)}
		{foreach $stylesheets as $sheeturl}<link href="{$sheeturl}" rel="stylesheet">{/foreach}
		{/if}

		<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
			<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->
	</head>

	<body>

{include file="navbar.tpl"}

		<div class="container">