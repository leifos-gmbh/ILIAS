<#1>
<?php
if ($ilDB->tableExists('adv_md_values_text') &&
    $ilDB->tableExists('adv_md_values_ltext')
) {
    // inserts all values from adv_md_values_text into adv_md_values_ltext WITHOUT
    // adv_md_values_ltext.value_index, ignoring duplicate entries.
    $ilDB->manipulate("
        INSERT IGNORE INTO adv_md_values_ltext (field_id, obj_id, `value`, value_index, disabled, sub_type, sub_id)
            SELECT val.field_id, val.obj_id, val.value, '', val.disabled, val.sub_type, val.sub_id
                FROM adv_md_values_text AS val
        ;
    ");

    // inserts all values from adv_md_values_text into adv_md_values_ltext WITH
    // adv_md_values_ltext.value_index, whereas the value_index will be the default
    // lang-code of adv_md_field_int because the old table didn't store this information.
    $ilDB->manipulate("
        INSERT IGNORE INTO adv_md_values_ltext (field_id, obj_id, `value`, value_index, disabled, sub_type, sub_id)
            SELECT val.field_id, val.obj_id, val.value, field.lang_code, val.disabled, val.sub_type, val.sub_id
                FROM adv_md_values_text AS val
                JOIN adv_md_field_int AS field ON field.field_id = val.field_id
        ;
    ");
}
?>
