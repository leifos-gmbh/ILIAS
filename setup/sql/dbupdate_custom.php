<#1>
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
<#2>
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
<#3>
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
