<#1>
<?php

if (!$ilDB->tableExists('adv_md_record_int')) {
    $ilDB->createTable('adv_md_record_int', [
        'record_id' => [
            'type' => ilDBConstants::T_INTEGER,
            'length' => 4,
            'notnull' => true
        ],
        'title' => [
            'type' => ilDBConstants::T_TEXT,
            'notnull' => false,
            'length' => 128
        ],
        'description' => [
            'type' => ilDBConstants::T_TEXT,
            'notnull' => false,
            'length' => 4000
        ],
        'lang_code' => [
            'type' => ilDBConstants::T_TEXT,
            'notnull' => true,
            'length' => 5
        ],
        'lang_default' => [
            'type' => ilDBConstants::T_INTEGER,
            'length' => 1,
            'notnull' => true
        ]
    ]);
    $ilDB->addPrimaryKey('adv_md_record_int', ['record_id', 'lang_code']);
}
?>
<#2>
<?php
// none
?>


<#3>
<?php

if (!$ilDB->tableExists('adv_md_field_int')) {
    $ilDB->createTable('adv_md_field_int', [
        'field_id' => [
            'type' => ilDBConstants::T_INTEGER,
            'length' => 4,
            'notnull' => true
        ],
        'title' => [
            'type' => ilDBConstants::T_TEXT,
            'notnull' => false,
            'length' => 128
        ],
        'description' => [
            'type' => ilDBConstants::T_TEXT,
            'notnull' => false,
            'length' => 4000
        ],
        'lang_code' => [
            'type' => ilDBConstants::T_TEXT,
            'notnull' => true,
            'length' => 5
        ],
        'lang_default' => [
            'type' => ilDBConstants::T_INTEGER,
            'length' => 1,
            'notnull' => true
        ]
    ]);
    $ilDB->addPrimaryKey('adv_md_field_int', ['field_id', 'lang_code']);
}
?>
<#4>
<?php

if ($ilDB->tableColumnExists('adv_md_record_int', 'lang_default')) {
    $ilDB->dropTableColumn('adv_md_record_int', 'lang_default');
}
?>
<#5>
<?php

if ($ilDB->tableColumnExists('adv_md_field_int', 'lang_default')) {
    $ilDB->dropTableColumn('adv_md_field_int', 'lang_default');
}
?>

<#6>
<?php

if (!$ilDB->tableColumnExists('adv_md_record','lang_default')) {
    $ilDB->addTableColumn('adv_md_record', 'lang_default', [
        'type' => 'text',
        'notnull' => false,
        'length' => 2,
        'default' => ''
    ]);

}
?>

