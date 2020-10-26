<#1>
<?php
if (!$ilDB->tableColumnExists('didactic_tpl_settings','icon_ide')) {
    $ilDB->addTableColumn('didactic_tpl_settings', 'icon_ide', [
        'type' => ilDBConstants::T_TEXT,
        'length' => 64,
        'notnull' => false,
        'default' => ''
    ]);
}
?>

