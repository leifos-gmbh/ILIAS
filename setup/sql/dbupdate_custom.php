<#1>
<?php
if (!$ilDB->tableColumnExists("skl_profile", "ref_id")) {
    $ilDB->addTableColumn("skl_profile", "ref_id", array(
        "type" => "integer",
        "notnull" => true,
        "default" => 0,
        "length" => 4
    ));
}
?>
<#2>
<?php
$ilCtrlStructureReader->getStructure();
?>
