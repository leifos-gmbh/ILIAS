
<?php


if($_SERVER['HTTP_HOST'] == 'sso.uni-muenster.de')
{
	$attr = base64_encode($_SERVER['HTTP_X_TRUSTED_REMOTE_ATTR']);
	$user = base64_encode($_SERVER['HTTP_X_TRUSTED_REMOTE_USER']);
	header('Location: https://unirep-online.de/sso/mssso.php?mssso_attr='.$attr.'&mssso_user='.$user);
	exit;
}

chdir ('..');
$cookie_path = dirname(dirname($_SERVER['PHP_SELF']));
define('IL_COOKIE_PATH', $cookie_path);

$_POST['username'] = 'mssso';
$_POST['password'] = 'dummy';




/**
 * LTI launch target script
 *
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 */
include_once './Services/Context/classes/class.ilContext.php';
ilContext::init(ilContext::CONTEXT_APACHE_SSO);

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

// authentication is done here ->
$ilCtrl->initBaseClass("ilStartUpGUI");
$ilCtrl->setCmd('doStandardAuthentication');
$ilCtrl->setTargetScript("ilias.php");
$ilCtrl->callBaseClass();

?>
