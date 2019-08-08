<#1>
<?php
	if (!$ilDB->tableExists("book_obj_use_book"))
	{
		$fields = array(
			"obj_id" => array(
				"type" => "integer",
				"notnull" => true,
				"length" => 4,
				"default" => 0
			),
			"book_obj_id" => array(
				"type" => "integer",
				"notnull" => true,
				"length" => 4,
				"default" => 0
			)
		);
	 	$ilDB->createTable("book_obj_use_book", $fields);
	 }
?>
<#2>
<?php
	$ilDB->addPrimaryKey("book_obj_use_book", array("obj_id", "book_obj_id"));
?>
<#3>
<?php
if(!$ilDB->tableColumnExists('booking_reservation','context_obj_id'))
{
	$ilDB->addTableColumn(
		'booking_reservation',
		'context_obj_id',
		array(
			'type' => 'integer',
			'length' => 1,
			'notnull' => false,
			'default' => 0
		));
}

?>
<#4>
<?php
$ilDB->dropTableColumn('booking_reservation', 'context_obj_id');

if(!$ilDB->tableColumnExists('booking_reservation','context_obj_id'))
{
	$ilDB->addTableColumn(
		'booking_reservation',
		'context_obj_id',
		array(
			'type' => 'integer',
			'length' => 4,
			'notnull' => false,
			'default' => 0
		));
}
?>
<#5>
<?php
$ilDB->renameTableColumn('book_obj_use_book', "book_obj_id", 'book_ref_id');
?>
<#6>
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
<#7>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#8>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
$read_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('read_learning_progress');
$edit_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('edit_learning_progress');
$write_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('write');
if($read_ops_id && $edit_ops_id)
{
	$lp_type_id = ilDBUpdateNewObjectType::getObjectTypeId('crsr');
	if($lp_type_id)
	{
		ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $read_ops_id);
		ilDBUpdateNewObjectType::addRBACOperation($lp_type_id, $edit_ops_id);
		ilDBUpdateNewObjectType::cloneOperation('crsr', $write_ops_id, $read_ops_id);
		ilDBUpdateNewObjectType::cloneOperation('crsr', $write_ops_id, $edit_ops_id);
	}
}
?>

<#9>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#10>
<?php
$ilCtrlStructureReader->getStructure();
?>
