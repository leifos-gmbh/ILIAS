<#1>
<?php
if (!$ilDB->tableColumnExists('wiki_user_html_export', 'with_comments')) {
    $ilDB->addTableColumn(
        'wiki_user_html_export',
        'with_comments',
        array(
            "type" => "integer",
            "notnull" => true,
            "length" => 1,
            "default" => 0
        )
    );
}
?>
<#2>
<?php
$ilDB->dropPrimaryKey('wiki_user_html_export');
$ilDB->addPrimaryKey('wiki_user_html_export', ['wiki_id', 'with_comments']);
?>