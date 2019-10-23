<#1>
<?php
if (!$ilDB->tableColumnExists('page_layout','mod_lm')) {
    $ilDB->addTableColumn(
        'page_layout',
        'mod_lm',
    array(
        'type'	=> 'integer',
        'length'=> 1,
        'notnull' => false
    ));
}
?>