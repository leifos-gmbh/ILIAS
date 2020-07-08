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
<#2>
<?php
if (!$ilDB->tableColumnExists('skl_profile_level', 'order_nr'))
{
    $ilDB->addTableColumn('skl_profile_level', 'order_nr', array(
        "type" => "integer",
        "notnull" => true,
        "default" => 0,
        "length" => 4
    ));
}
?>
