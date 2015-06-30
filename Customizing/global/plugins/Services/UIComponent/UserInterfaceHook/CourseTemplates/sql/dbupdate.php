<#1>
<?php

if( !$ilDB->tableExists('crs_templates') )
{
	$ilDB->createTable('crs_templates', array(
		'crs_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'usr_id' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		),
		'tstamp' => array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		)
	));
	
	$ilDB->addPrimaryKey('crs_templates', array('crs_id'));
}

?>
<#2>
<?php

if (!$ilDB->tableColumnExists('crs_templates', 'parent'))
{
	$ilDB->addTableColumn('crs_templates', 'parent', array(
		"type" => "integer",
		"notnull" => false,
		"length" => 4
	));
}
	
?>