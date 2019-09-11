<#1>
<?php

if(!$ilDB->tableExists('wfld_user_setting'))
{
	$ilDB->createTable('wfld_user_setting', array(
		'user_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'wfld_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'sortation' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0,
		)
	));
	$ilDB->addPrimaryKey('wfld_user_setting',array('user_id','wfld_id'));
}

?>
