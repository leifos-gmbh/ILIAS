<#1>
<?php
if ($ilDB->tableExists('cont_skills')) {
    $set = $ilDB->query(
        "SELECT id, skill_id, tref_id FROM cont_skills"
    );
}

if ($ilDB->tableExists('skl_usage')) {
    while ($rec = $ilDB->fetchAssoc($set)) {
        $ilDB->replace(
            'skl_usage',
            array(
                'obj_id' => array('integer', $rec['id']),
                'skill_id' => array('integer', $rec['skill_id']),
                'tref_id' => array('integer', $rec['tref_id'])
            ),
            array()
        );
    }
}
?>
