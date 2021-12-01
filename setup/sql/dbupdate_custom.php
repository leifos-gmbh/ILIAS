<#1>
<?php
if (!$ilDB->tableExists('sty_rep_container')) {
    $fields = [
        'ref_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'reuse' => [
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ]
    ];

    $ilDB->createTable('sty_rep_container', $fields);
    $ilDB->addPrimaryKey('sty_rep_container', ['ref_id']);
}
?>
<#2>
<?php
$set = $ilDB->queryF("SELECT * FROM content_object ",
    [],
    []
);
while ($rec = $ilDB->fetchAssoc($set)) {
    $ilDB->replace(
        "style_usage",
        array(
            "obj_id" => array("integer", (int) $rec["id"])),
        array(
            "style_id" => array("integer", (int) $rec["stylesheet"]))
    );
}
?>
<#3>
<?php
$set = $ilDB->queryF("SELECT * FROM content_page_data ",
    [],
    []
);
while ($rec = $ilDB->fetchAssoc($set)) {
    $ilDB->replace(
        "style_usage",
        array(
            "obj_id" => array("integer", (int) $rec["content_page_id"])),
        array(
            "style_id" => array("integer", (int) $rec["stylesheet"]))
    );
}
?>
<#4>
<?php
if (!$ilDB->tableColumnExists('style_data', 'owner_obj')) {
    $ilDB->addTableColumn('style_data', 'owner_obj', array(
        'type' => 'integer',
        'notnull' => false,
        'length' => 4,
        'default' => 0
    ));
}
?>
<#5>
<?php
$set = $ilDB->queryF("SELECT * FROM style_data WHERE standard = %s",
    ["integer"],
    [0]
);
while ($rec = $ilDB->fetchAssoc($set)) {
    $set2 = $ilDB->queryF("SELECT * FROM style_usage " .
        " WHERE style_id = %s ",
        ["integer"],
        [$rec["id"]]
    );
    while ($rec2 = $ilDB->fetchAssoc($set2)) {
        $ilDB->update("style_data", [
            "owner_obj" => ["integer", $rec2["obj_id"]]
        ], [    // where
                "id" => ["integer", $rec["id"]]
            ]
        );

    }
}
?>