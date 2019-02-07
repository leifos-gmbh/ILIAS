<#1>
<?php
if (!$ilDB->tableColumnExists('exc_assignment_peer', 'is_valid'))
{
	$ilDB->addTableColumn('exc_assignment_peer', 'is_valid', array(
		"type" => "integer",
		"notnull" => true,
		"length" => 1,
		"default" => 0
	));
}
?>