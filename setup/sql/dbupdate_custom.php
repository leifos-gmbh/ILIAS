<#1>
<?php
if(!$ilDB->tableColumnExists('grp_settings', 'grp_start'))
{
		$ilDB->addTableColumn('grp_settings', 'grp_start', array(
			"type" => "integer",
			"notnull" => false,
			"length" => 4
		));
}
if(!$ilDB->tableColumnExists('grp_settings', 'grp_end'))
{
		$ilDB->addTableColumn('grp_settings', 'grp_end', array(
			"type" => "integer",
			"notnull" => false,
			"length" => 4
		));
}
?>
<#2>
<?php
if (!$ilDB->tableExists('comp_impl_int'))
{
	$ilDB->createTable('comp_impl_int', array(
		'provider_component' => array(
			'type' => 'text',
			'length' => 150,
			'notnull' => true
		),
		'provider_interface' => array(
			'type' => 'text',
			'length' => 50,
			'notnull' => true
		),
		'consumer_component' => array(
			'type' => 'text',
			'length' => 150,
			'notnull' => true
		),
		'consumer_dir' => array(
			'type' => 'text',
			'length' => 200,
			'notnull' => true
		),
		'consumer_classbase' => array(
			'type' => 'text',
			'length' => 50,
			'notnull' => false
		)
	));
	$ilDB->addIndex('comp_impl_int',array('provider_component','provider_interface'),'i1');
}
?>
<#3>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4>
<?php
if(!$ilDB->tableColumnExists('comp_impl_int', 'consumer_dir'))
{
	$ilDB->addTableColumn('comp_impl_int', 'consumer_dir', array(
		'type' => 'text',
		'length' => 200,
		'notnull' => true
	));
}
?>
<#5>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#6>
<?php
if(!$ilDB->tableColumnExists('crs_settings', 'show_members_export'))
{
		$ilDB->addTableColumn('crs_settings', 'show_members_export', array(
			"type" => "integer",
			"notnull" => false,
			"length" => 4
		));
}
?>

