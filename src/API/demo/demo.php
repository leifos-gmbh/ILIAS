<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

chdir("../../..");
require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

global $DIC;

$actor_id = $DIC->user()->getId();

// this will currently "succeed" if actor id and course ref id are 7

$actor_id = 7;

$api = $DIC->api();
$add_member_cmd = $api->course(7)->membership()->add(100, 200);

try {
	$api->dispatch($add_member_cmd, $actor_id);
}
catch (Exception $e)
{
	echo "Oh no, an exception: ".$e->getMessage();
}

?>