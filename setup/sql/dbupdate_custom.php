<#1>
<?php
//
?>
<#2>
<?php
//
?>
<#3>
<?php

global $ilDB;

if (!$ilDB->tableColumnExists('obj_content_master_lng', 'fallback_lang')) {
    $ilDB->addTableColumn(
        'obj_content_master_lng',
        'fallback_lang',
        array(
            "type" => "text",
            "notnull" => false,
            "length" => 2
        )
    );
}

?>
