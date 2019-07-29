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
<#6>
<?php
$ilCtrlStructureReader->getStructure();
?>
