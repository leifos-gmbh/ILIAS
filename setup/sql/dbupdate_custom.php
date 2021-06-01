<#1>
<?php
if (!$ilDB->tableExists('style_char_title')) {
    $fields = [
        'type' => [
            'type' => 'text',
            'length' => 30,
            'notnull' => true
        ],
        'characteristic' => [
            'type' => 'text',
            'length' => 30,
            'notnull' => true
        ],
        'lang' => [
            'type' => 'text',
            'length' => 2,
            'notnull' => true
        ],
        'title' => [
            'type' => 'text',
            'length' => 200,
            'notnull' => false
        ]
    ];

    $ilDB->createTable('style_char_title', $fields);
    $ilDB->addPrimaryKey('style_char_title', ['type', 'characteristic', 'lang']);
}
?>
<#2>
<?php
    $ilDB->dropPrimaryKey('style_char_title');
    if (!$ilDB->tableColumnExists('style_char_title', 'style_id')) {
        $ilDB->addTableColumn('style_char_title', 'style_id', array(
            "type" => "integer",
            "notnull" => true,
            "length" => 4
        ));
    }
    $ilDB->addPrimaryKey('style_char_title', ['style_id', 'type', 'characteristic', 'lang']);
?>
<#3>
<?php
if (!$ilDB->tableColumnExists('style_char', 'order_nr')) {
    $ilDB->addTableColumn('style_char', 'order_nr', array(
        "type" => "integer",
        "notnull" => true,
        "length" => 4,
        "default" => 0
    ));
}
?>
<#4>
<?php
if (!$ilDB->tableColumnExists('style_char', 'deprecated')) {
    $ilDB->addTableColumn('style_char', 'deprecated', array(
        "type" => "integer",
        "notnull" => true,
        "length" => 1,
        "default" => 0
    ));
}
?>
