<#1>
<?php
if(!$ilDB->tableColumnExists('exc_assignment','deadline_mode'))
{
    $ilDB->addTableColumn(
        'exc_assignment',
        'deadline_mode',
        array(
            'type' => 'integer',
			'length' => 1,
            'notnull' => false,
            'default' => 0
        ));
}
?>
<#2>
<?php
if(!$ilDB->tableColumnExists('exc_assignment','relative_deadline'))
{
    $ilDB->addTableColumn(
        'exc_assignment',
        'relative_deadline',
        array(
            'type' => 'integer',
			'length' => 4,
            'notnull' => false,
            'default' => 0
        ));
}
?>
<#3>
<?php
if(!$ilDB->tableColumnExists('exc_idl','starting_ts'))
{
    $ilDB->addTableColumn(
        'exc_idl',
        'starting_ts',
        array(
            'type' => 'integer',
			'length' => 4,
            'notnull' => false,
            'default' => 0
        ));
}
?>