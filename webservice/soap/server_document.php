<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* SOAP server
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @package ilias
*/

//ini_set("soap.wsdl_cache_enabled", 0);

chdir("../..");
define("ILIAS_MODULE", "webservice/soap");
define("IL_SOAPMODE_NUSOAP", 0);
define("IL_SOAPMODE_INTERNAL", 1);

// php7 only SOAPMODE_INTERNAL
define('IL_SOAPMODE', IL_SOAPMODE_INTERNAL);
include_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_SOAP);

require_once("./Services/Init/classes/class.ilIniFile.php");
$ilIliasIniFile = new ilIniFile("./ilias.ini.php");
$ilIliasIniFile->read();

if ((bool) $ilIliasIniFile->readVariable('https', 'auto_https_detect_enabled')) {
    $headerName = $ilIliasIniFile->readVariable('https', 'auto_https_detect_header_name');
    $headerValue = $ilIliasIniFile->readVariable('https', 'auto_https_detect_header_value');

    $headerName = "HTTP_" . str_replace("-", "_", strtoupper($headerName));
    if (strcasecmp($_SERVER[$headerName], $headerValue) == 0) {
        $_SERVER['HTTPS'] = 'on';
    }
}

if (strcasecmp($_SERVER["REQUEST_METHOD"], "post") == 0) {
    // This is a SOAP request
    include_once('webservice/soap/include/inc.soap_functions.php');
    $uri = ilSoapFunctions::buildHTTPPath() . '/webservice/soap/server.php';
    $wsdl = $uri . '?wsdl';

    $soapServer = new SoapServer($wsdl, array('uri' => $uri));

    include_once './webservice/soap/classes/class.ilSoapRequestHandler.php';
    $soapServer->setObject(new \ilSoapRequestHandler($soapServer));
    $soapServer->handle();
} else {
    // This is a request to display the available SOAP methods or WSDL...
    include('webservice/soap/nusoapserver_document.php');
}
