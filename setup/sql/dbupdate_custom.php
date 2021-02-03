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
<#2>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#3>
<?php
if (!$ilDB->tableExists("skl_profile_completion")) {
    $fields = [
        "profile_id" => [
            "type"    => "integer",
            "length"  => 4,
            "notnull" => true
        ],
        "user_id" => [
            "type"     => "integer",
            "length"   => 4,
            "notnull"  => true
        ],
        "date" => [
            "type"     => "timestamp",
            "notnull"  => true
        ],
        "fulfilled" => [
            "type" => "integer",
            "length" => 1,
            "notnull" => true
        ]
    ];
    $ilDB->createTable("skl_profile_completion", $fields);
    $ilDB->addPrimaryKey("skl_profile_completion", ["profile_id", "user_id", "date"]);
}
?>
