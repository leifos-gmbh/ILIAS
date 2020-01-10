<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


if (!defined('ILIAS_MODULE') || (defined('ILIAS_MODULE') && ILIAS_MODULE != "webservice/soap")) {
    //direct call to this endpoint
    chdir("../..");
    define("ILIAS_MODULE", "webservice/soap");
    define("IL_SOAPMODE_NUSOAP", 0);
    define("IL_SOAPMODE_INTERNAL", 1);
    define("IL_SOAPMODE", IL_SOAPMODE_NUSOAP);
}

include_once './webservice/soap/classes/class.ilNusoapUserAdministrationAdapter.php';
$server = new ilNusoapUserAdministrationAdapter(true, true);
$server->start();
