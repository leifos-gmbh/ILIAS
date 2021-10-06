<#1>
    //
?>
<#2>
//
?>
<#3>
//
?>
<#4>
//
?>
<#5>
//
?>
<#6>
//
?>
<#7>
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
