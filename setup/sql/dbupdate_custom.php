<#1>
<?php
if (!$ilDB->tableColumnExists("skl_profile", "image_id")) {
    $ilDB->addTableColumn("skl_profile", "image_id", array(
        "type" => "text",
        "notnull" => false,
        "length" => 4000
    ));
}
?>