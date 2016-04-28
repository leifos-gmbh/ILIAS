<#1>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#2>
<?php
	if (!$ilDB->tableColumnExists('skl_tree_node', 'creation_date'))
	{
		$ilDB->addTableColumn('skl_tree_node', 'creation_date', array(
				"type" => "timestamp",
				"notnull" => false,
		));
	}
?>
<#3>
<?php
if (!$ilDB->tableColumnExists('skl_tree_node', 'import_id'))
{
	$ilDB->addTableColumn('skl_tree_node', 'import_id', array(
			"type" => "text",
			"length" => 50,
			"notnull" => false
	));
}
?>
<#4>
<?php
if (!$ilDB->tableColumnExists('skl_level', 'creation_date'))
{
	$ilDB->addTableColumn('skl_level', 'creation_date', array(
			"type" => "timestamp",
			"notnull" => false,
	));
}
?>
<#5>
<?php
if (!$ilDB->tableColumnExists('skl_level', 'import_id'))
{
	$ilDB->addTableColumn('skl_level', 'import_id', array(
			"type" => "text",
			"length" => 50,
			"notnull" => false
	));
}
?>
