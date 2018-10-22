<#1>
<?php
	//
?>
<#2>
<?php
	//
?>
<#3>
<?php
if (!$ilDB->tableColumnExists('usr_data', 'first_login'))
{
	$ilDB->addTableColumn('usr_data', 'first_login', array(
		"type" => "timestamp",
		"notnull" => false
	));

	// since we do not have this date for existing users we take the minimum of last login
	// and first access to any repo object
	$set = $ilDB->queryF("SELECT u.usr_id, u.last_login, min(r.first_access) first_access FROM usr_data u LEFT JOIN read_event r ON (u.usr_id = r.usr_id) GROUP BY u.usr_id, u.last_login",
		array(),
		array()
		);
	while ($rec = $ilDB->fetchAssoc($set))
	{
		$first_login = $rec["last_login"];
		if ($rec["first_access"] != "" && ($rec["first_access"] < $rec["last_login"]))
		{
			$first_login = $rec["first_access"];
		}

		if ($first_login != "")
		{
			$ilDB->update("usr_data", array(
				"first_login" => array("timestamp", $first_login)
			), array(    // where
				"usr_id" => array("integer", $rec["usr_id"])
			));
		}
	}
}
?>
<#4>
<?php
if (!$ilDB->tableColumnExists('usr_data', 'last_profile_prompt'))
{
	$ilDB->addTableColumn('usr_data', 'last_profile_prompt', array(
		"type" => "timestamp",
		"notnull" => false
	));
}
?>
<#5>
<?php
	$ilCtrlStructureReader->getStructure();
?>
