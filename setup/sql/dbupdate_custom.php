<#1>
<?php
if (!$ilDB->tableExists("exc_ass_wiki_team"))
{
	$fields = array(
		"id" => array(
			"type" => "integer",
			"notnull" => true,
			"length" => 4,
			"default" => 0
		),
		"container_ref_id" => array(
			"type" => "integer",
			"notnull" => true,
			"length" => 4,
			"default" => 0
		),
		"template_ref_id" => array(
			"type" => "integer",
			"notnull" => true,
			"length" => 4,
			"default" => 0
		)
	);
 	$ilDB->createTable("exc_ass_wiki_team", $fields);
 	$ilDB->addPrimaryKey("exc_ass_wiki_team", array("id"));
}
?>
<#2>
<?php
	$ilCtrlStructureReader->getStructure();
?>
