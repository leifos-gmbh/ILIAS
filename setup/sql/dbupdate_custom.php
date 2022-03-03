<#1>
<?php
if (!$ilDB->tableColumnExists('ecs_part_settings', 'username_placeholder')) {
    $ilDB->addTableColumn(
        'ecs_part_settings',
        'username_placeholder',
        [
            'type' => ilDBConstants::T_TEXT,
            'notnull' => false,
            'length' => 250,
            'default' => ''
        ]
    );
}
?>
<#2>
<?php
if (!$ilDB->tableColumnExists('ecs_part_settings', 'user_auth_mode')) {
    $ilDB->addTableColumn(
        'ecs_part_settings',
        'user_auth_mode',
        [
            'type' => ilDBConstants::T_TEXT,
            'notnull' => false,
            'length' => 16,
            'default' => ''
        ]
    );
}
?>
<#3>
<?php
if (!$ilDB->tableExists('ecs_user_consent')) {
    $ilDB->createTable('ecs_user_consent', [
        'usr_id' => [
            'type' => ilDBConstants::T_INTEGER,
            'length' => 4,
            'notnull' => true
        ],
        'mid' => [
            'type' => ilDBConstants::T_INTEGER,
            'length' => 4,
            'notnull' => true,
        ]
    ]);
    $ilDB->addPrimaryKey('ecs_user_consent', ['usr_id', 'mid']);
}
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
if (!$ilDB->tableColumnExists('ecs_part_settings', 'incoming_local_accounts')) {
    $ilDB->addTableColumn(
        'ecs_part_settings',
        'incoming_local_accounts',
        [
            'type' => ilDBConstants::T_INTEGER,
            'notnull' => true,
            'length' => 1,
            'default' => 1
        ]
    );
}
?>

<#7>
<?php
if (!$ilDB->tableColumnExists('ecs_part_settings', 'outgoing_auth_mode')) {
    $ilDB->addTableColumn(
        'ecs_part_settings',
        'outgoing_auth_mode',
        [
            'type' => ilDBConstants::T_TEXT,
            'notnull' => false,
            'length' => 64,
            'default' => ''
        ]
    );
}
?>
