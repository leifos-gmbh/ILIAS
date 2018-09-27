<#1>
<?php
if(!$ilDB->tableColumnExists("il_object_def", "offline_handling"))
{
	$def = array(
		'type'    => 'integer',
		'length'  => 1,
		'notnull' => true,
		'default' => 0
	);
	$ilDB->addTableColumn("il_object_def", "offline_handling", $def);
}
?>
<#2>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#3>
<?php
if(!$ilDB->tableColumnExists('object_data', 'offline'))
{
	$def = [
		'type' => 'integer',
		'length' => 1,
		'notnull' => false,
		'default' => null
	];
	$ilDB->addTableColumn('object_data', 'offline', $def);
}
?>

