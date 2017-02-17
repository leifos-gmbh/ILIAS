<#1>
<?php
if (!$ilDB->tableExists("usr_starting_point"))
{
	$ilDB->createTable("usr_starting_point", array(
		"id" => array(
			"type" => "integer",
			"length" => 4,
			"notnull" => true,
			"default" => 0
		),
		"position" => array(
			"type" => "integer",
			"length" => 4,
			"notnull" => false,
			"default" => 0
		),
		"starting_point" => array (
			"type" => "integer",
			"length" => 4,
			"notnull" => false,
			"default" => 0
		),
		"starting_object" => array (
			"type" => "integer",
			"length" => 4,
			"notnull" => false,
			"default" => 0
		),
		"rule_type" => array (
			"type" => "integer",
			"length" => 4,
			"notnull" => false,
			"default" => 0
		),
		"rule_options" => array (
			"type" => "text",
			"length" => 4000,
			"notnull" => false,
		)
	));

	$ilDB->addPrimaryKey('usr_starting_point', array('id'));
	$ilDB->createSequence('usr_starting_point');
}
?>