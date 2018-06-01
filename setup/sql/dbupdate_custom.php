<#1>
<?php
	if (!$ilDB->tableColumnExists('booking_settings', 'reminder_status'))
	{
		$ilDB->addTableColumn('booking_settings', 'reminder_status', array(
			"type" => "integer",
			"notnull" => true,
			"length" => 1,
			"default" => 0
		));
	}
?>
<#2>
<?php
	if (!$ilDB->tableColumnExists('booking_settings', 'reminder_day'))
	{
		$ilDB->addTableColumn('booking_settings', 'reminder_day', array(
			"type" => "integer",
			"notnull" => true,
			"length" => 4,
			"default" => 0
		));
	}
?>
<#3>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#4>
<?php
	if (!$ilDB->tableColumnExists('booking_settings', 'last_remind_ts'))
	{
		$ilDB->addTableColumn('booking_settings', 'last_remind_ts', array(
			"type" => "integer",
			"notnull" => true,
			"length" => 4,
			"default" => 0
		));
	}
?>
