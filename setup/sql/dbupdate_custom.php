<#1>
<?php
if(!$ilDB->tableColumnExists('exc_data','nr_mandatory_random'))
{
    $ilDB->addTableColumn(
        'exc_data',
        'nr_mandatory_random',
        array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ));
}
?>
<#2>
<?php

if( !$ilDB->tableExists('exc_mandatory_random') )
{
    $ilDB->createTable('exc_mandatory_random', array(
        'exc_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'usr_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'ass_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
    ));

    $ilDB->addPrimaryKey('exc_mandatory_random', array('exc_id', 'usr_id', 'ass_id'));
}

?>
<#3>
<?php
$ilCtrlStructureReader->getStructure();
?>