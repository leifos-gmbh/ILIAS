<#1>
<?php
if(!$ilDB->tableExists('crs_reference_settings'))
{
	$ilDB->createTable('crs_reference_settings', [
		'obj_id' => [
			'type' => 'integer',
			'length' => 4,
			'notnull' => true,
			'default' => 0
		],
		'member_update' => [
			'type' => 'integer',
			'length' => 1,
			'notnull' => true,
			'default' => 0
		]
	]);
}
?>
<#2>
<?php
$ilCtrlStructureReader->getStructure();
?>

