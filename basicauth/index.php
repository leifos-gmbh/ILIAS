<?php

chdir('..');

// ... which is AUTH_APACHE_PLUGIN
$_POST['auth_mode'] = 13;
$_POST['username'] = 'dummy';
$_POST['password'] = 'dummy';

$GLOBALS['COOKIE_PATH'] = '/';
unset($_COOKIE['PHPSESSID']);


require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

$redirect = ILIAS_HTTP_PATH.'/goto.php?';
if($_GET['target'])
{
	$redirect .= 'target='.$_GET['target'];
}
else
{
	$redirect .= 'target=root_1';
}

$redirect = str_replace('/basicauth','', $redirect);


ilUtil::redirect($redirect);

?>
