<?php

if( PHP_SAPI != 'cli' ) die('this script is to be used by command line interface only!');

if(version_compare(PHP_VERSION, '5.5.0', '>='))
{
	error_reporting((E_ALL & ~E_NOTICE) & ~E_DEPRECATED & ~E_STRICT);
}
else if (version_compare(PHP_VERSION, '5.3.0', '>='))
{
	error_reporting((E_ALL & ~E_NOTICE) & ~E_DEPRECATED);
}
else
{
	error_reporting(E_ALL & ~E_NOTICE);
}

ini_set('display_errors', 'on');


chdir(dirname(__FILE__).'/..');

require_once('ClientReplication/classes/class.ilClientReplicator.php');

$icr = new ilClientReplicator();

$icr->run('ClientReplication/config.ini.php');


exit;
