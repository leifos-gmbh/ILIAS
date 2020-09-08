<#1>
<?php
if (!$ilDB->tableColumnExists('skl_user_skill_level', 'next_level_fulfilment')) {
    $ilDB->addTableColumn("skl_user_skill_level", "next_level_fulfilment", array(
        "type" => "float",
        "notnull" => true,
        "default" => 0.0
    ));
}

if (!$ilDB->tableColumnExists('skl_user_has_level', 'next_level_fulfilment')) {
    $ilDB->addTableColumn("skl_user_has_level", "next_level_fulfilment", array(
        "type" => "float",
        "notnull" => true,
        "default" => 0.0
    ));
}
?>
<#2>
<?php
if (!$ilDB->tableColumnExists('skl_profile_level', 'order_nr'))
{
    $ilDB->addTableColumn('skl_profile_level', 'order_nr', array(
        "type" => "integer",
        "notnull" => true,
        "default" => 0,
        "length" => 4
    ));
}
?>
<#3>
<?php
if ($ilDB->tableExists('skl_profile_level')) {
    $profiles = ilSkillProfile::getProfiles();
    if (!empty($profiles)) {
        foreach ($profiles as $id => $profile) {
            $set = $ilDB->query(
                "SELECT profile_id, base_skill_id, tref_id, order_nr FROM skl_profile_level WHERE " .
                " profile_id = " . $ilDB->quote($id, "integer")
            );
            $cnt = 1;
            while ($rec = $ilDB->fetchAssoc($set)) {
                $ilDB->manipulate(
                    "UPDATE skl_profile_level SET " .
                    " order_nr = " . $ilDB->quote(($cnt * 10), "integer") .
                    " WHERE profile_id = " . $ilDB->quote($rec["profile_id"], "integer") .
                    " AND base_skill_id = " . $ilDB->quote($rec["base_skill_id"], "integer") .
                    " AND tref_id = " . $ilDB->quote($rec["tref_id"], "integer")
                );
                $cnt++;
            }
        }
    }
}
?>
<#4>
<?php
if (!$ilDB->tableColumnExists('svy_qblk', 'compress_view')) {
    $ilDB->addTableColumn('svy_qblk', 'compress_view', array(
        "type" => "integer",
        "length" => 1,
        "notnull" => true,
        "default" => 0
    ));
}
?>
