
<?php

chdir ('..');

$attributes = base64_encode($_SERVER['HTTP_X_TRUSTED_REMOTE_ATTR']);
$user = base64_encode($_SERVER['HTTP_X_TRUSTED_REMOTE_USER']);

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
