<?php

use ILIAS\MetaData\OERExposer\OAIPMH\Handler;

/*
 * Handles OAI-PMH request according to https://www.openarchives.org/OAI/openarchivesprotocol.html
 */

include_once './Services/Context/classes/class.ilContext.php';
include_once './Services/Init/classes/class.ilInitialisation.php';
ilContext::init(ilContext::CONTEXT_ICAL);
ilInitialisation::initILIAS();

include_once './Services/MetaData/classes/OERExposer/OAIPMH/Handler.php';
$handler = new Handler();
$handler->sendResponseToRequest();
