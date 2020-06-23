<#1>
<?php
if (!$ilDB->tableColumnExists('skl_user_skill_level', 'next_level_fulfilment')) {
    $ilDB->addTableColumn("skl_user_skill_level", "next_level_fulfilment", array(
        "type" => "float",
        "notnull" => true,
        "default" => 0.0
    ));
}

if (!$ilDB->tableColumnExists('skl_user_has_level', 'next_level_fulfilment')) {
    $ilDB->addTableColumn("skl_user_has_level", "next_level_fulfilment", array(
        "type" => "float",
        "notnull" => true,
        "default" => 0.0
    ));
}
?>