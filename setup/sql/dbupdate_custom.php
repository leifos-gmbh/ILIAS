<#1>
<?php
if (!$ilDB->tableColumnExists('svy_qblk', 'compress_view')) {
    $ilDB->addTableColumn('svy_qblk', 'compress_view', array(
        "type" => "integer",
        "length" => 1,
        "notnull" => true,
        "default" => 0
    ));
}
?>