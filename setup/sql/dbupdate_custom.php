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
<#7>
<?php

if (!$ilDB->tableExists('adv_md_values_ltext')) {
    $ilDB->createTable('adv_md_values_ltext', [
        'obj_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'sub_type' => [
            'type' => 'text',
            'length' => 10,
            'notnull' => true,
            'default' => "-"
        ],
        'sub_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'field_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'value_index' => [
            'type' => ilDBConstants::T_TEXT,
            'length' => 16,
            'notnull' => true,
        ],
        'value' => [
            'type' => ilDBConstants::T_TEXT,
            'length' => 4000,
            'notnull' => false
        ]
    ]);

    $ilDB->addPrimaryKey('adv_md_values_ltext', array('obj_id', 'sub_type', 'sub_id', 'field_id', 'value_index'));
}
?>
<#8>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#9>
<?php
if (!$ilDB->tableExists('adv_md_values_enum')) {
    $ilDB->createTable('adv_md_values_enum', [
        'obj_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'sub_type' => [
            'type' => 'text',
            'length' => 10,
            'notnull' => true,
            'default' => "-"
        ],
        'sub_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'field_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'disabled' => [
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ],
        'value_index' => [
            'type' => ilDBConstants::T_TEXT,
            'length' => 16,
            'notnull' => true,
        ]
    ]);

    $ilDB->addPrimaryKey('adv_md_values_enum', array('obj_id', 'sub_type', 'sub_id', 'field_id', 'value_index'));
}
?>
<#10>
<?php

$query = 'select field_id, field_type, field_values from adv_mdf_definition ' .
    'where field_type = 1  or field_type = 8 ';
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $values = unserialize($row->field_values);
    if (!is_array($values)) {
        continue;
    }
    $options = $values;

    $query = 'select * from adv_md_values_text ' .
        'where field_id = ' . $ilDB->quote($row->field_id, ilDBConstants::T_INTEGER);
    $val_res = $ilDB->query($query);
    while ($val_row = $val_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {

        $query = 'select * from adv_md_values_enum ' .
            'where obj_id = ' . $ilDB->quote($val_row->obj_id, ilDBConstants::T_INTEGER) . ' ' .
            'and sub_id = ' . $ilDB->quote($val_row->sub_id, ilDBConstants::T_INTEGER) . ' ' .
            'and sub_type = ' . $ilDB->quote($val_row->sub_type, ilDBConstants::T_TEXT) . ' ' .
            'and field_id = ' . $ilDB->quote($val_row->field_id, ilDBConstants::T_INTEGER);
        $exists_res = $ilDB->query($query);
        if ($exists_res->numRows()) {
            //ilLoggerFactory::getLogger('root')->info('field_id: ' . $val_row->field_id . ' is already migrated');
            continue;
        }
        $current_values = [];
        if (strpos($val_row->value, '~|~') === 0) {
            // multi enum
            $current_values = explode('~|~', $val_row->value);
            array_pop($current_values);
            array_shift($current_values);

        } else {
            $current_values[] = (string) $val_row->value;
        }
        //ilLoggerFactory::getLogger('root')->dump($current_values);
        $positions = [];
        foreach ($current_values as $value) {
            if (!strlen(trim($value))) {
                continue;
            }
            $idx = array_search($value, $options);
            if ($idx === false) {
                continue;
            }
            $positions[] = $idx;
        }

        //ilLoggerFactory::getLogger('root')->dump($positions);
        foreach ($positions as $pos) {

            $query = 'insert into adv_md_values_enum (obj_id, sub_type, sub_id, field_id, value_index, disabled) ' .
            'values ( ' .
                $ilDB->quote($val_row->obj_id, ilDBConstants::T_INTEGER) . ', '.
                $ilDB->quote($val_row->sub_type, ilDBConstants::T_TEXT) . ', ' .
                $ilDB->quote($val_row->sub_id, ilDBConstants::T_INTEGER) . ', ' .
                $ilDB->quote($val_row->field_id, ilDBConstants::T_INTEGER) . ', ' .
                $ilDB->quote($pos, ilDBConstants::T_INTEGER) . ', ' .
                $ilDB->quote($val_row->disabled, ilDBConstants::T_INTEGER)
                .' ) ';
            $ilDB->query($query);
        }

    }
}
?>


