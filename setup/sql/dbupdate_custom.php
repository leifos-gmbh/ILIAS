<#1>
<?php

// [old] coll_manual => [new] coll_manual
$ilDB->manipulate("UPDATE ut_lp_settings".
	" SET u_mode = ".$ilDB->quote(16, "integer").
	" WHERE u_mode = ".$ilDB->quote(15, "integer"));

// [old] coll_tlt => [new] coll_tlt
$ilDB->manipulate("UPDATE ut_lp_settings".
	" SET u_mode = ".$ilDB->quote(15, "integer").
	" WHERE u_mode = ".$ilDB->quote(14, "integer"));

?>
<#2>
<?php

// set all surveys without access codes who are set to ANONYMIZE_ON (1) to ANONYMIZE_FREEACCESS (2)

$ilDB->manipulate("UPDATE svy_svy svy".
	" INNER JOIN (".
		"SELECT DISTINCT(svy.survey_id) survey_id".
		" FROM svy_svy svy".
		" LEFT JOIN svy_anonymous anon ON (anon.survey_fi = svy.survey_id)".
		" WHERE svy.anonymize = ".$ilDB->quote(1, "integer").
		" AND anon.survey_fi IS NULL".
	") sub ON (sub.survey_id = svy.survey_id)".
	" SET svy.anonymize = ".$ilDB->quote(2, "integer"));

?>
<#3>
<?php

if (!$ilDB->tableColumnExists("file_data", "locked_by"))
{
	$ilDB->addTableColumn("file_data", "locked_by", array(
		"type" => "integer",
		"notnull" => false,
		"length" => 4,
		"default" => 0));
}

?>
<#4>
<?php

if (!$ilDB->tableColumnExists("file_data", "locked_until"))
{
	$ilDB->addTableColumn("file_data", "locked_until", array(
		"type" => "integer",
		"notnull" => false,
		"length" => 4,
		"default" => 0));
}

?>
<#5>
<?php

if (!$ilDB->tableColumnExists("file_data", "locked_download"))
{
	$ilDB->addTableColumn("file_data", "locked_download", array(
		"type" => "integer",
		"notnull" => false,
		"length" => 1,
		"default" => 0));
}

?>
<#6>
<?php
	$ilCtrlStructureReader->getStructure();
?>
<#7>
<?php
/*
$ilDB->query(
	  'update webr_items set target = '
	. 'replace(target,'.$ilDB->quote('lms.skyguide.corp','text').','.$ilDB->quote('lms.skyguide.ch','text')
	. ')'
);
 */

?>