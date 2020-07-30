<?php

//set_include_path("./Services/PEAR/lib".PATH_SEPARATOR.ini_get('include_path'));
require_once("libs/composer/vendor/autoload.php");

//define ("DEVMODE", 0);

/*$_POST['action'] = "save_testdata";
$_POST['user_id'] = 2;
$_POST['test_id'] = "pt_en_de";
$_POST['test_duration'] = "00:10:05";
$_POST['test_finished'] = 1;
$_POST['result_points'] = 6.3;
$_POST['result_level'] = "B1";
$_POST['result_grammar'] = "60";
$_POST['result_voca'] = "70";
$_POST['result_read'] = "44";
$_POST['result_listen'] = "11";
$_POST['user_id'] = "6349179172610e5e381f2e77dc038ab2";*/

if ($_POST['action'] == "save_testdata")
{
	include_once("./Services/Init/classes/class.ilErrorHandling.php");
	include_once("./Services/Database/classes/class.ilDBWrapperFactory.php");
	include_once("./Services/Utilities/classes/class.ilUtil.php");

	include_once("./Services/CD/classes/class.ilCDTestSaver.php");
	include_once("./Services/CD/classes/class.ilCDTestSaverInit.php");

	include_once("./Services/Init/classes/class.ilInitialisation.php");
	$i = new ilCDTestSaverInit();
	$i->init();

	ilCDTestSaver::saveTestResults();
}

?>