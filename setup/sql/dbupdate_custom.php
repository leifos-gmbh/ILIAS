<#1>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#2>
<?php
if(!$ilDB->tableColumnExists('exc_assignment','rel_deadline_last_subm'))
{
    $ilDB->addTableColumn(
        'exc_assignment',
        'rel_deadline_last_subm',
        array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ));
}
?>
