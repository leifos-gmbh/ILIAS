<#1>
<?php
if (!$ilDB->tableColumnExists('booking_settings', 'preference_nr'))
{
    $ilDB->addTableColumn('booking_settings', 'preference_nr', array(
        "type" => "integer",
        "notnull" => true,
        "length" => 4,
        "default" => 0
    ));
}
?>
<#2>
<?php
if (!$ilDB->tableColumnExists('booking_settings', 'pref_deadline'))
{
    $ilDB->addTableColumn('booking_settings', 'pref_deadline', array(
        "type" => "integer",
        "notnull" => true,
        "length" => 4,
        "default" => 0
    ));
}
?>
<#3>
<?php
if( !$ilDB->tableExists('booking_preferences') )
{
    $ilDB->createTable('booking_preferences', array(
        'book_pool_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'user_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),
        'book_obj_id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        )
    ));
    $ilDB->addPrimaryKey('booking_preferences', ['book_pool_id', 'user_id', 'book_obj_id']);
}
?>
<#4>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#5>
<?php
if (!$ilDB->tableColumnExists('booking_settings', 'pref_booking_hash'))
{
    $ilDB->addTableColumn('booking_settings', 'pref_booking_hash', array(
        "type" => "text",
        "notnull" => true,
        "length" => 23,
        "default" => "0"
    ));
}
?>
<#6>
<?php
$ilCtrlStructureReader->getStructure();
?>
