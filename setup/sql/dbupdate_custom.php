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
