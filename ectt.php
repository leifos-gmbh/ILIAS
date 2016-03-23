<?php

	header("Content-Type: text/html; charset=utf-8");

	include_once 'include/inc.header.php';
	

	ini_set('display_errors','on');
	error_reporting(E_ALL ^ E_NOTICE);
	
	require_once 'Plugins/ElisCustomTrackingTool/classes/class.ElisCustomTrackingToolGUI.php';
	$myApplication = new ElisCustomTrackingToolGUI();
	$myApplication->run();

?>
