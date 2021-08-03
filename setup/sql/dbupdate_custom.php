<#1>
<?php
$field_infos = [
    'type' => ilDBConstants::T_INTEGER,
    'default' => 0,
    'notnull' => true,
    'length' => 8
];
$ilDB->modifyTableColumn('ecs_course_assignments', 'cms_id', $field_infos);
?>