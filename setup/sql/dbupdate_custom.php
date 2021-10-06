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
// Create migration table
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
