<#1>
<?php
if ($ilDB->tableExists('role_data'))
{
	if(!$ilDB->tableColumnExists('starting_point'))
	{
		$ilDB->addTableColumn("role_data", "starting_point", array("type" => "integer", "length" => 4, "notnull" => false, "default" => 0));
	}

	if(!$ilDB->tableColumnExists('starting_object'))
	{
		$ilDB->addTableColumn("role_data", "starting_object", array("type" => "integer", "length" => 4, "notnull" => false, "default" => 0));
	}

	if(!$ilDB->tableColumnExists('starting_position'))
	{
		$ilDB->addTableColumn("role_data", "starting_position", array("type" => "integer", "length" => 4, "notnull" => false, "default" => 0));
	}
}