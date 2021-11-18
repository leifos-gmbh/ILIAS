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
//
?>
<#4>
<?php
//
?>
<#5>
<?php
//
?>
<#6>
<?php
//
?>
<#7>
<?php
//
?>
<#8>
<?php
if (!$ilDB->tableExists('prtf_role_assignment')) {
    $fields = [
        'role_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'template_ref_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ]
    ];

    $ilDB->createTable('prtf_role_assignment', $fields);
    $ilDB->addPrimaryKey('prtf_role_assignment', ['role_id', 'template_ref_id']);
}
?>
<#9>
<?php
if (!$ilDB->tableColumnExists('skl_user_skill_level', 'trigger_user_id')) {
    $ilDB->addTableColumn('skl_user_skill_level', 'trigger_user_id', array(
        'type' => 'text',
        'notnull' => true,
        'length' => 20,
        'default' => "-"
    ));
}
?>
<#10>
<?php
if (!$ilDB->tableColumnExists('skl_user_has_level', 'trigger_user_id')) {
    $ilDB->addTableColumn('skl_user_has_level', 'trigger_user_id', array(
        'type' => 'text',
        'notnull' => true,
        'length' => 20,
        'default' => "-"
    ));
}
?>
