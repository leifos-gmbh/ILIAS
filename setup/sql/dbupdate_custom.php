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
