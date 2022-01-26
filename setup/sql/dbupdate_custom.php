<#1>
<?php
if (!$ilDB->tableColumnExists('il_media_cast_data', 'autoplaymode')) {
    $ilDB->addTableColumn('il_media_cast_data', 'autoplaymode', array(
        "type" => "integer",
        "notnull" => true,
        "length" => 1,
        "default" => 0
    ));
}

?>
<#2>
<?php
if (!$ilDB->tableColumnExists('il_media_cast_data', 'nr_initial_videos')) {
    $ilDB->addTableColumn('il_media_cast_data', 'nr_initial_videos', array(
        "type" => "integer",
        "notnull" => true,
        "length" => 1,
        "default" => 0
    ));
}

?>
<#3>
<?php
if (!$ilDB->tableColumnExists('media_item', 'duration')) {
    $ilDB->addTableColumn('media_item', 'duration', array(
        "type" => "integer",
        "notnull" => true,
        "length" => 4,
        "default" => 0
    ));
}

?>
<#4>
<?php
if (!$ilDB->tableColumnExists('il_media_cast_data', 'new_items_in_lp')) {
    $ilDB->addTableColumn('il_media_cast_data', 'new_items_in_lp', array(
        "type" => "integer",
        "notnull" => true,
        "length" => 1,
        "default" => 1
    ));
}

?>
<#5>
<?php
    $ilCtrlStructureReader->getStructure();
?>