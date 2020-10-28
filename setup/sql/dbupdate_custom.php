<#1>
<?php
if (!$ilDB->tableColumnExists('usr_starting_point', 'calendar_view')) {
    $ilDB->addTableColumn("usr_starting_point", "calendar_view", array(
        "type" => ilDBConstants::T_INTEGER,
        "notnull" => true,
        "default" => 0
    ));
}

if (!$ilDB->tableColumnExists('usr_starting_point', 'calendar_period')) {
    $ilDB->addTableColumn("usr_starting_point", "calendar_period", array(
        "type" => ilDBConstants::T_INTEGER,
        "notnull" => true,
        "default" => 0
    ));
}
?>
<#2>
<?php

if (!$ilDB->tableColumnExists('event', 'show_cannot_part')) {
    $ilDB->addTableColumn(
        'event',
        'show_cannot_part',
        [
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ]
    );
}
?>

<#3>
<?php
if (!$ilDB->tableColumnExists('grp_settings', 'session_limit')) {
    $ilDB->addTableColumn("grp_settings", "session_limit", array(
        "type" => "integer",
        'length' => 1,
        "notnull" => true,
        "default" => 0
    ));
}
?>
<#4>
<?php
if (!$ilDB->tableColumnExists('grp_settings', 'session_prev')) {
    $ilDB->addTableColumn("grp_settings", "session_prev", array(
        "type" => "integer",
        'length' => 8,
        "notnull" => true,
        "default" => -1
    ));
}
?>
<#5>
<?php
if (!$ilDB->tableColumnExists('grp_settings', 'session_next')) {
    $ilDB->addTableColumn("grp_settings", "session_next", array(
        "type" => "integer",
        'length' => 8,
        "notnull" => true,
        "default" => -1
    ));
}
?>
