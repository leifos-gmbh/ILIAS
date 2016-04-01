<#1>
<?php

if(!$ilDB->tableExists('exc_idl'))
{
	$ilDB->createTable('exc_idl', array(
		'ass_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),	
		'member_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'is_team' => array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		),	
		'tstamp' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false,
			'default' => 0
		)
	));

	$ilDB->addPrimaryKey('exc_idl', array('ass_id', 'member_id', 'is_team'));
}

?>