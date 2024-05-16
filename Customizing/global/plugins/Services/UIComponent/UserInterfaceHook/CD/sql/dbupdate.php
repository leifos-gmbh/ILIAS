<#1>
<?php
//
?>
<#2>
<?php
//
?>
<#3>
<?php
//
?>
<#4>
<?php
$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'title' => array(
        'type' => 'text',
        'length' => 200,
        'notnull' => false
        )
);
$ilDB->createTable('cd_center', $fields);

?>
<#5>
<?php
    $ilDB->addPrimaryKey("cd_center", array("id"));
    $ilDB->createSequence('cd_center');
?>
<#6>
<?php
//
?>
<#7>
<?php
//
?>
<#8>
<?php
$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'center_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'title' => array(
        'type' => 'text',
        'length' => 200,
        'notnull' => false
        ),
    'street' => array(
        'type' => 'text',
        'length' => 200,
        'notnull' => false
        ),
    'postal_code' => array(
        'type' => 'text',
        'length' => 20,
        'notnull' => false
        ),
    'city' => array(
        'type' => 'text',
        'length' => 200,
        'notnull' => false
        ),
    'country' => array(
        'type' => 'text',
        'length' => 200,
        'notnull' => false
        ),
    'po_box' => array(
        'type' => 'text',
        'length' => 100,
        'notnull' => false
        ),
    'fax' => array(
        'type' => 'text',
        'length' => 100,
        'notnull' => false
        ),
    'contact_firstname' => array(
        'type' => 'text',
        'length' => 100,
        'notnull' => false
        ),
    'contact_lastname' => array(
        'type' => 'text',
        'length' => 100,
        'notnull' => false
        ),
    'contact_tel' => array(
        'type' => 'text',
        'length' => 100,
        'notnull' => false
        )
);
$ilDB->createTable('cd_company', $fields);
$ilDB->addPrimaryKey("cd_company", array("id"));
$ilDB->createSequence('cd_company');

?>
<#9>
<?php

$ilDB->addTableColumn("cd_company", "company_password", array(
    "type" => "text",
    "notnull" => false,
    "length" => 20
));

?>
<#10>
<?php
//
?>
<#11>
<?php
$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'user_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'ts' => array(
        'type' => 'timestamp'
        ),
    'course_lang' => array(
        'type' => 'text',
        'length' => 2,
        'notnull' => true
        ),
    'olang_sel' => array(
        'type' => 'text',
        'length' => 200,
        'notnull' => false
        ),
    'olang_free' => array(
        'type' => 'text',
        'length' => 200,
        'notnull' => false
        ),
    'last_course' => array(
        'type' => 'text',
        'length' => 4,
        'notnull' => false
        ),
    'lang_level' => array(
        'type' => 'text',
        'length' => 2,
        'notnull' => false
        ),
    'talk_understanding' => array(
        'type' => 'text',
        'length' => 400,
        'notnull' => false
        ),
    'write_read' => array(
        'type' => 'text',
        'length' => 400,
        'notnull' => false
        ),
    'tech_lang_sel' => array(
        'type' => 'text',
        'length' => 400,
        'notnull' => false
        ),
    'tech_lang_free' => array(
        'type' => 'text',
        'length' => 100,
        'notnull' => false
        )
);
$ilDB->createTable('cd_needs_analysis', $fields);
$ilDB->addPrimaryKey("cd_needs_analysis", array("id"));
$ilDB->createSequence('cd_needs_analysis');

?>
<#12>
<?php
$ilDB->addTableColumn("cd_needs_analysis", "last_update", array(
    "type" => "timestamp"
    ));
?>
<#13>
<?php
$ilDB->addTableColumn("cd_center", "category", array(
    "type" => "integer",
    "notnull" => false,
    "length" => 4
    ));
?>
<#14>
<?php
$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'user_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'ts' => array(
        'type' => 'timestamp'
        ),
    'target_lang' => array(
        'type' => 'text',
        'length' => 2,
        'notnull' => true
        ),
    'in_lang' => array(
        'type' => 'text',
        'length' => 2,
        'notnull' => true
        ),
    'duration' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'finished' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'points' => array(
        'type' => 'float',
        'notnull' => true
        ),
    'result_level' => array(
        'type' => 'text',
        'length' => 2,
        'notnull' => true
        ),
    'result_grammar' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'result_voca' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'result_read' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'result_listen' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        )
    );
$ilDB->createTable('cd_ext_test', $fields);
$ilDB->addPrimaryKey("cd_ext_test", array("id"));
$ilDB->createSequence('cd_ext_test');
?>
<#15>
<?php
$fields = array(
    'session_id' => array(
        'type' => 'text',
        'length' => 80,
        'notnull' => true
        ),
    'user_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'ts' => array(
        'type' => 'timestamp'
        ),
    'sess_data' => array(
        'type' => 'clob'
        )
    );
$ilDB->createTable('cd_ext_test_sess', $fields);
$ilDB->addIndex("cd_ext_test_sess", array("session_id"), "sii");

?>
<#16>
<?php
?>
<#17>
<?php

$ilDB->addTableColumn("cd_company", "internet", array(
    "type" => "text",
    "notnull" => false,
    "length" => 200
    ));

?>
<#18>
<?php
//
?>
<#19>
<?php
$fields = array(
    'user_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'lang' => array(
        "type" => "text",
        "notnull" => true,
        "length" => 2
        ),
    'target' => array(
        "type" => "text",
        "notnull" => false,
        "length" => 2
        ),
    'oral' => array(
        "type" => "text",
        "notnull" => false,
        "length" => 2
        )
    );
$ilDB->createTable('cd_oral_target', $fields);
$ilDB->addPrimaryKey("cd_oral_target", array("user_id", "lang"));

?>
<#20>
<?php
$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'title' => array(
        'type' => 'text',
        'length' => 200,
        'notnull' => false
        ),
    'lang' => array(
        'type' => 'text',
        'length' => 2,
        'notnull' => false
        )
);
$ilDB->createTable('cd_course_type', $fields);

?>
<#21>
<?php
    $ilDB->addPrimaryKey("cd_course_type", array("id"));
    $ilDB->createSequence('cd_course_type');
?>
<#22>
<?php

$ilDB->addTableColumn(
    "cd_oral_target",
    "course_type",
    array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        )
);

?>
<#23>
<?php

$ilDB->addTableColumn(
    "cd_company",
    "parent_company",
    array(
        'type' => 'integer',
        'length' => 4,
        'default' => 0,
        'notnull' => true
        )
);

?>
<#24>
<?php

$ilDB->addTableColumn(
    "cd_company",
    "creation_user",
    array(
        'type' => 'integer',
        'length' => 4,
        'default' => 0,
        'notnull' => true
        )
);

?>
<#25>
<?php

$ilDB->addTableColumn(
    "cd_company",
    "title_amendment",
    array(
            'type' => 'clob'
        )
);

?>
<#26>
<?php

$ilDB->addTableColumn(
    "cd_company",
    "address_amendment",
    array(
            'type' => 'clob'
        )
);

?>
<#27>
<?php
$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'title' => array(
        'type' => 'text',
        'length' => 200,
        'notnull' => false
        ),
    'filename' => array(
        'type' => 'text',
        'length' => 200,
        'notnull' => false
        )
);
$ilDB->createTable('cd_part_conf_temp', $fields);

?>
<#28>
<?php
    $ilDB->addPrimaryKey("cd_part_conf_temp", array("id"));
    $ilDB->createSequence('cd_part_conf_temp');
?>
<#29>
<?php
$fields = array(
    'id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'participant_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'trainer_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'course_id' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true
        ),
    'after_x_lessons' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true,
        'default' => 0
        ),
    'starting_cef' => array(
        "type" => "text",
        "notnull" => true,
        "length" => 2
        ),
    'current_cef' => array(
        "type" => "text",
        "notnull" => true,
        "length" => 2
        ),
    'skills' => array(
        "type" => "text",
        "notnull" => false,
        "length" => 4000
        ),
    'test_attendance' => array(
        'type' => 'integer',
        'length' => 1,
        'notnull' => true,
        'default' => 0
        ),
    'test_result' => array(
        "type" => "text",
        "notnull" => false,
        "length" => 4000
        ),
    'stay_in_group' => array(
        'type' => 'integer',
        'length' => 4,
        'notnull' => true,
        'default' => 0
        ),
    'recommended_level' => array(
        "type" => "text",
        "notnull" => true,
        "length" => 2
        ),
    'creation_date' => array(
        "type" => "timestamp",
        "notnull" => true
        ),
    'update_date' => array(
        "type" => "timestamp",
        "notnull" => true
        )
    );
$ilDB->createTable('cd_participant_eval', $fields);
$ilDB->addPrimaryKey("cd_participant_eval", array("id"));

?>
<#30>
<?php
    $ilDB->createSequence('cd_participant_eval');
?>
<#31>
<?php
    //
?>
<#32>
<?php
    $ilDB->dropTableColumn('cd_participant_eval', "recommended_level");

    $ilDB->addTableColumn(
        'cd_participant_eval',
        "recommended_level",
        array(
        "type" => "text",
        "notnull" => false,
        "length" => 2
        )
    );
?>
<#33>
<?php
    $ilDB->addTableColumn(
    'cd_participant_eval',
    "test_level",
    array(
        "type" => "text",
        "notnull" => false,
        "length" => 1000
        )
);
?>
<#34>
<?php
    $ilDB->modifyTableColumn(
    'cd_participant_eval',
    'starting_cef',
    array("type" => "text", "length" => 10, "notnull" => false)
);
    $ilDB->modifyTableColumn(
        'cd_participant_eval',
        'current_cef',
        array("type" => "text", "length" => 10, "notnull" => false)
    );
    $ilDB->modifyTableColumn(
        'cd_participant_eval',
        'recommended_level',
        array("type" => "text", "length" => 10, "notnull" => false)
    );
?>
<#35>
<?php
    $ilDB->addTableColumn(
    'cd_participant_eval',
    "published",
    array(
            "type" => "integer",
            "length" => 1,
            "notnull" => true,
            "default" => 0
        )
);
?>
<#36>
<?php
    $ilDB->addTableColumn(
    'cd_oral_target',
    "tdate",
    array(
            "type" => "date",
            "notnull" => false
        )
);
?>
<#37>
<?php
    //
?>
<#38>
<?php
    $ilDB->addTableColumn(
    'cd_oral_target',
    "id",
    array(
            "type" => "integer",
            "notnull" => true,
            "length" => 4,
            "default" => 0
        )
);
    $ilDB->createSequence('cd_oral_target');
?>
<#39>
<?php
    $set = $ilDB->query(
    "SELECT * FROM cd_oral_target "
);
    while ($rec = $ilDB->fetchAssoc($set)) {
        $id = $ilDB->nextId("cd_oral_target");
        $m = "UPDATE cd_oral_target SET " .
            " id = " . $ilDB->quote($id, "integer") . "," .
            " tdate = " . $ilDB->quote(date('Y-m-d'), "date") .
            " WHERE user_id = " . $ilDB->quote($rec["user_id"], "integer") .
            " AND lang = " . $ilDB->quote($rec["lang"], "text");
        $ilDB->manipulate($m);
    }
?>
<#40>
<?php
    $ilDB->dropPrimaryKey("cd_oral_target");
    $ilDB->addPrimaryKey("cd_oral_target", array("id"));
?>
<#41>
<?php
$ilDB->addTableColumn("cd_center", "email", array(
    "type" => "text",
    "notnull" => false,
    "length" => 200,
    "default" => ''
    ));
?>
<#42>
<?php
    # dummy step to synch with daf branch again
?>
<#43>
<?php
    if (!$ilDB->tableExists("cd_needs_analysis_daf")) {
        $fields = array(
            'na_id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
            ),
            'q_id' => array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true
            ),
            'answer' => array(
                'type' => 'clob'
            )
        );
        $ilDB->createTable('cd_needs_analysis_daf', $fields);
        $ilDB->addPrimaryKey("cd_needs_analysis_daf", array("na_id", "q_id"));
        $ilDB->createSequence('cd_needs_analysis_daf');
    }
?>
<#44>
<?php
if (!$ilDB->tableColumnExists('cd_center', 'email')) {
    $ilDB->addTableColumn("cd_center", "email", array(
        "type" => "text",
        "notnull" => false,
        "length" => 200,
        "default" => ''
    ));
}
?>