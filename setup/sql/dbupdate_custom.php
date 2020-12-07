<#1>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#2>
<?php
if (!$ilDB->tableColumnExists('svy_svy', 'reminder_tmpl'))
{
	$ilDB->addTableColumn('svy_svy', 'reminder_tmpl', array(
		"type" => "integer",
		"notnull" => false,
		"length" => 4
	));
}
?>
<#3>
<?php
if (!$ilDB->tableColumnExists('svy_svy', 'tutor_res_status'))
{
	$ilDB->addTableColumn('svy_svy', 'tutor_res_status', array(
		"type" => "integer",
		"notnull" => false,
		"length" => 1
	));
}
if (!$ilDB->tableColumnExists('svy_svy', 'tutor_res_reci'))
{
	$ilDB->addTableColumn('svy_svy', 'tutor_res_reci', array(
		'type' => 'text',
		'length'  => 2000,
		'notnull' => false,
		'fixed' => false
	));
}
?>
<#4>
<?php
if (!$ilDB->tableColumnExists('svy_svy', 'tutor_res_cron'))
{
	$ilDB->addTableColumn('svy_svy', 'tutor_res_cron', array(
		"type" => "integer",
		"notnull" => false,
		"length" => 1
	));
}
?>
<#5>
<?php

// get tst type id
$row = $ilDB->fetchAssoc($ilDB->queryF(
	"SELECT obj_id FROM object_data WHERE type = %s AND title = %s",
	array('text', 'text'), array('typ', 'pdts')
));
$pdts_id = $row['obj_id'];

// register new 'object' rbac operation for tst
$op_id = $ilDB->nextId('rbac_operations');
$ilDB->insert('rbac_operations', array(
	'ops_id' => array('integer', $op_id),
	'operation' => array('text', 'change_presentation'),
	'description' => array('text', 'change presentation of a view'),
	'class' => array('text', 'object'),
	'op_order' => array('integer', 200)
));
$ilDB->insert('rbac_ta', array(
	'typ_id' => array('integer', $pdts_id),
	'ops_id' => array('integer', $op_id)
));

?>
<#6>
<?php
// We should ensure that settings are set for new installations and ILIAS version upgrades
$setting = new ilSetting();

$setting->set('pd_active_sort_view_0', serialize(['location', 'type']));
$setting->set('pd_active_sort_view_1', serialize(['location', 'type', 'start_date']));
$setting->set('pd_active_pres_view_0', serialize(['list', 'tile']));
$setting->set('pd_active_pres_view_1', serialize(['list', 'tile']));
$setting->set('pd_def_pres_view_0', 'list');
$setting->set('pd_def_pres_view_1', 'list');
?>
<#7>
<?php
if (!$ilDB->tableColumnExists('svy_svy', 'calculate_sum_score'))
{
    $ilDB->addTableColumn('svy_svy', 'calculate_sum_score', array(
        "type" => "integer",
        "notnull" => true,
        "default" => 0,
        "length" => 1
    ));
}
?>