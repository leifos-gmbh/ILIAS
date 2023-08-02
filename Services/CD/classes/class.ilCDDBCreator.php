<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Database/classes/class.ilDBAnalyzer.php");

/**
 * This class handles all DB changes necessary for Carl Duisberg
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 */
class ilCDDBCreator
{
    public function createTables()
    {
        global $ilDB, $ilSetting;

        $cd_db = $ilSetting->get("cd_db");

        // STEP 1
        if ($cd_db <= 0) {
            // add company, center, branch, profession, field_of_responsibility
            $fields = array(
                    'company_id' => array(
                            'type' => 'integer',
                            'length' => 4,
                            'notnull' => true,
                            'default' => 0
                    ),
                    'center_id' => array(
                            'type' => 'integer',
                            'length' => 4,
                            'notnull' => true,
                            'default' => 0
                    ),
                    'branch' => array(
                            'type' => 'text',
                            'length' => 200,
                            'notnull' => false
                    ),
                    'profession' => array(
                            'type' => 'text',
                            'length' => 200,
                            'notnull' => false
                    ),
                    'field_of_responsibility' => array(
                            'type' => 'text',
                            'length' => 200,
                            'notnull' => false
                    )
            );
            
            foreach ($fields as $f => $def) {
                $ilDB->addTableColumn("usr_data", $f, $def);
            }

            $ilSetting->set("cd_db", 1);
        }

        // STEP 2
        if ($cd_db <= 1) {
            $this->reloadControlStructure();
            $ilSetting->set("cd_db", 2);
        }

        // STEP 3
        if ($cd_db <= 2) {
            $this->reloadControlStructure();
            $ilSetting->set("cd_db", 3);
        }

        // STEP 4
        if ($cd_db <= 3) {
            /*
            $ilDB->addTableColumn("skl_tree_node", "self_eval", array(
                "type" => "integer",
                "length" => 1,
                "notnull" => true,
                "default" => 0
                ));*/

            $ilSetting->set("cd_db", 4);
        }

        // STEP 5
        if ($cd_db <= 4) {
            $this->reloadControlStructure();
            $ilSetting->set("cd_db", 5);
        }

        // STEP 6
        if ($cd_db <= 5) {
            /*
            // skill self evaluation table
            $fields = array(
                    'id' => array(
                            'type' => 'integer',
                            'length' => 4,
                            'notnull' => true,
                            'default' => 0
                    ),
                    'user_id' => array(
                            'type' => 'integer',
                            'length' => 4,
                            'notnull' => true,
                            'default' => 0
                    ),
                    'top_skill_id' => array(
                            'type' => 'integer',
                            'length' => 4,
                            'notnull' => true,
                            'default' => 0
                    ),
                    'created' => array(
                            'type' => 'timestamp',
                            'notnull' => true,
                    ),
                    'last_update' => array(
                            'type' => 'timestamp',
                            'notnull' => true,
                    )
            );

            $ilDB->createTable('skl_self_eval', $fields);
            $ilDB->addPrimaryKey("skl_self_eval", array("id"));
            $ilDB->createSequence('skl_self_eval');
            */

            $ilSetting->set("cd_db", 6);
        }


        // STEP 7
        if ($cd_db <= 6) {
            /*
            // skill self evaluation table
            $fields = array(
                    'self_eval_id' => array(
                            'type' => 'integer',
                            'length' => 4,
                            'notnull' => true,
                            'default' => 0
                    ),
                    'skill_id' => array(
                            'type' => 'integer',
                            'length' => 4,
                            'notnull' => true,
                            'default' => 0
                    ),
                    'level_id' => array(
                            'type' => 'integer',
                            'length' => 4,
                            'notnull' => true,
                            'default' => 0
                    )
            );

            $ilDB->createTable('skl_self_eval_level', $fields);
            */

            $ilSetting->set("cd_db", 7);
        }

        // STEP 8
        if ($cd_db <= 7) {
            /*
            $ilDB->addPrimaryKey("skl_self_eval_level",
                array("self_eval_id", "skill_id"));
            */
            $ilSetting->set("cd_db", 8);
        }


        // STEP 9
        if ($cd_db <= 8) {
            global $rbacadmin;
            include_once './Services/AccessControl/classes/class.ilObjRole.php';

            $roles = array(
                "CDC-SysAdmin" => "y",
                "CDC-MA-Master-Chief" => "y",
                "CDC-MA-GF" => "n",
                "CDC-Admin" => "y",
                "Kunde-TN" => "n",
                "Kunde-PER" => "n",
                "Kunde-PER-Master" => "n");

            foreach ($roles as $r => $p) {
                $role = new ilObjRole();
                $role->setTitle($r);
                $role->setAllowRegister(false);
                $role->toggleAssignUsersStatus(false);
                $role->create();

                $rbacadmin->assignRoleToFolder($role->getId(), 8, 'y');
                $rbacadmin->setProtected(
                    8,
                    $role->getId(),
                    $p
                );
            }

            $ilSetting->set("cd_db", 9);
        }

        // STEP 10
        if ($cd_db <= 9) {
            // add company, center, branch, profession, field_of_responsibility
            $fields = array(
                    'course_type' => array(
                            'type' => 'integer',
                            'length' => 4,
                            'notnull' => true,
                            'default' => 0
                    )
            );
            
            foreach ($fields as $f => $def) {
                $ilDB->addTableColumn("crs_settings", $f, $def);
            }

            $ilSetting->set("cd_db", 10);
        }

        // STEP 11
        if ($cd_db <= 10) {
            $ilDB->modifyTableColumn("crs_settings", 'course_type', array(
                            'type' => 'integer',
                            'length' => 4,
                            'notnull' => false,
                            'default' => 0
                    ));

            $ilSetting->set("cd_db", 11);
        }

        // STEP 12
        if ($cd_db <= 11) {
            if (!$ilDB->tableColumnExists('usr_data', 'password_addon')) {
                // add company, center, branch, profession, field_of_responsibility
                $fields = array(
                        'password_addon' => array(
                                'type' => 'text',
                                'length' => 40,
                                'notnull' => false
                        )
                );
                
                foreach ($fields as $f => $def) {
                    $ilDB->addTableColumn("usr_data", $f, $def);
                }
            }

            $ilSetting->set("cd_db", 12);
        }
        
        // STEP 13
        if ($cd_db <= 12) {
            $this->reloadControlStructure();
            $ilSetting->set("cd_db", 13);
        }
        
        // STEP 14
        if ($cd_db <= 13) {
            // course level
            $fields = array(
                    'course_level' => array(
                            'type' => 'text',
                            'length' => 2,
                            'notnull' => false
                    )
            );
            
            foreach ($fields as $f => $def) {
                $ilDB->addTableColumn("crs_settings", $f, $def);
            }
            
            $ilSetting->set("cd_db", 14);
        }
        
        // STEP 15
        if ($cd_db <= 14) {
            // course level
            $fields = array(
                    'course_nr' => array(
                            'type' => 'text',
                            'length' => 64,
                            'notnull' => false
                    )
            );
            
            foreach ($fields as $f => $def) {
                $ilDB->addTableColumn("crs_settings", $f, $def);
            }
            
            $ilSetting->set("cd_db", 15);
        }
        
        // STEP 16
        if ($cd_db <= 15) {
            // course level
            $fields = array(
                    'id' => array(
                            'type' => 'integer',
                            'length' => 4,
                            'notnull' => true
                    ),
                    'residence_permit' => array(
                            'type' => 'integer',
                            'length' => 1,
                            'notnull' => true,
                            'default' => 0
                    ),
                    'work_permit' => array(
                            'type' => 'integer',
                            'length' => 1,
                            'notnull' => true,
                            'default' => 0
                    ),
                    'driving_license' => array(
                            'type' => 'integer',
                            'length' => 1,
                            'notnull' => true,
                            'default' => 0
                    ),
                    'car_owner' => array(
                            'type' => 'integer',
                            'length' => 1,
                            'notnull' => true,
                            'default' => 0
                    ),
                    'work_exp_lang_train' => array(
                            'type' => 'clob'
                    ),
                    'given_lessons' => array(
                            'type' => 'integer',
                            'length' => 1,
                            'notnull' => true,
                            'default' => 0
                    ),
                    'work_experience_other' => array(
                            'type' => 'clob'
                    ),
                    'general_education' => array(
                            'type' => 'text',
                            'length' => 40,
                            'notnull' => false
                    ),
                    'general_education_other' => array(
                            'type' => 'text',
                            'length' => 1000,
                            'notnull' => false
                    ),
                    'lang_education' => array(
                            'type' => 'text',
                            'length' => 40,
                            'notnull' => false
                    ),
                    'lang_education_other' => array(
                            'type' => 'text',
                            'length' => 1000,
                            'notnull' => false
                    ),
                    'teaching_experience' => array(
                            'type' => 'text',
                            'length' => 4000,
                            'notnull' => false
                    ),
                    'other_tech_lang' => array(
                            'type' => 'text',
                            'length' => 1000,
                            'notnull' => false
                    ),
                    'cefr_knowledge' => array(
                            'type' => 'integer',
                            'length' => 1,
                            'notnull' => true,
                            'default' => 0
                    ),
                    'favorite_cefr_levels' => array(
                            'type' => 'text',
                            'length' => 200,
                            'notnull' => false
                    ),
                    'cefr_exam_prep_license' => array(
                            'type' => 'text',
                            'length' => 200,
                            'notnull' => false
                    ),
                    'other_competences' => array(
                            'type' => 'text',
                            'length' => 4000,
                            'notnull' => false
                    ),
                    'current_teaching_material' => array(
                            'type' => 'clob'
                    ),
                    'teaching_media' => array(
                            'type' => 'text',
                            'length' => 200,
                            'notnull' => false
                    ),
                    'other_teaching_media' => array(
                            'type' => 'text',
                            'length' => 1000,
                            'notnull' => false
                    ),
                    'teaching_methods' => array(
                            'type' => 'clob'
                    ),
                    'trainer_role' => array(
                            'type' => 'clob'
                    ),
                    'my_potential' => array(
                            'type' => 'clob'
                    ),
                    'scenario' => array(
                            'type' => 'clob'
                    ),
                    'teaching_location' => array(
                            'type' => 'text',
                            'length' => 2000,
                            'notnull' => false
                    )
            );

            $ilDB->createTable('cd_trainer', $fields);
            $ilDB->addPrimaryKey("cd_trainer", array("id"));
            
            $ilSetting->set("cd_db", 16);
        }

        // STEP 17
        if ($cd_db <= 16) {
            // course level
            $fields = array(
                    'id' => array(
                            'type' => 'text',
                            'length' => 4,
                            'notnull' => true
                    ),
                    'lang' => array(
                            'type' => 'text',
                            'length' => 10,
                            'notnull' => true
                    )
            );

            $ilDB->createTable('cd_trainer_mtl', $fields);
            $ilDB->addPrimaryKey("cd_trainer_mtl", array("id", "lang"));
            
            $ilSetting->set("cd_db", 17);
        }

        // STEP 18
        if ($cd_db <= 17) {
            // course level
            $fields = array(
                    'id' => array(
                            'type' => 'text',
                            'length' => 4,
                            'notnull' => true
                    ),
                    'lang' => array(
                            'type' => 'text',
                            'length' => 10,
                            'notnull' => true
                    )
            );

            $ilDB->createTable('cd_trainer_il', $fields);
            $ilDB->addPrimaryKey("cd_trainer_il", array("id", "lang"));
            
            $ilSetting->set("cd_db", 18);
        }

        // STEP 19
        if ($cd_db <= 18) {
            // course level
            $fields = array(
                    'id' => array(
                            'type' => 'text',
                            'length' => 4,
                            'notnull' => true
                    ),
                    'lang' => array(
                            'type' => 'text',
                            'length' => 10,
                            'notnull' => true
                    ),
                    'slevel' => array(
                            'type' => 'text',
                            'length' => 10,
                            'notnull' => true
                    )
            );

            $ilDB->createTable('cd_trainer_ls', $fields);
            $ilDB->addPrimaryKey("cd_trainer_ls", array("id", "lang"));
            
            $ilSetting->set("cd_db", 19);
        }

        // STEP 20
        if ($cd_db <= 19) {
            // course level
            $fields = array(
                    'id' => array(
                            'type' => 'text',
                            'length' => 4,
                            'notnull' => true
                    ),
                    'year' => array(
                            'type' => 'integer',
                            'length' => 4,
                            'notnull' => true
                    ),
                    'description' => array(
                            'type' => 'text',
                            'length' => 4000,
                            'notnull' => false
                    )
            );

            $ilDB->createTable('cd_trainer_fe', $fields);
            $ilDB->addPrimaryKey("cd_trainer_fe", array("id", "year"));
            
            $ilSetting->set("cd_db", 20);
        }
        
        // STEP 21
        if ($cd_db <= 20) {
            // course level
            $fields = array(
                    'id' => array(
                            'type' => 'text',
                            'length' => 4,
                            'notnull' => true
                    ),
                    'disc' => array(
                            'type' => 'integer',
                            'length' => 4,
                            'notnull' => true
                    ),
                    'val' => array(
                            'type' => 'integer',
                            'length' => 1,
                            'notnull' => true
                    )
            );

            $ilDB->createTable('cd_trainer_tl', $fields);
            $ilDB->addPrimaryKey("cd_trainer_tl", array("id", "disc"));
            
            $ilSetting->set("cd_db", 21);
        }
        
        // STEP 22
        if ($cd_db <= 21) {
            // teach type experience
            $fields = array(
                    'id' => array(
                            'type' => 'text',
                            'length' => 4,
                            'notnull' => true
                    ),
                    'type' => array(
                            'type' => 'integer',
                            'length' => 4,
                            'notnull' => true
                    ),
                    'val' => array(
                            'type' => 'integer',
                            'length' => 1,
                            'notnull' => true
                    )
            );

            $ilDB->createTable('cd_trainer_tt', $fields);
            $ilDB->addPrimaryKey("cd_trainer_tt", array("id", "type"));
            
            $ilSetting->set("cd_db", 22);
        }
        
        // STEP 23
        if ($cd_db <= 22) {
            // teach type experience
            $fields = array(
                    'id' => array(
                            'type' => 'text',
                            'length' => 4,
                            'notnull' => true
                    ),
                    'license' => array(
                            'type' => 'text',
                            'length' => 10,
                            'notnull' => true
                    ),
                    'end_date' => array(
                            'type' => 'date',
                            'notnull' => true
                    )
            );

            $ilDB->createTable('cd_trainer_el', $fields);
            $ilDB->addPrimaryKey("cd_trainer_el", array("id", "license"));
            
            $ilSetting->set("cd_db", 23);
        }
        
        // STEP 24
        if ($cd_db <= 23) {
            // add center to trainer
            $fields = array(
                'center_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true,
                    'default' => 0
                )
            );
            
            foreach ($fields as $f => $def) {
                $ilDB->addTableColumn("cd_trainer", $f, $def);
            }
            $ilSetting->set("cd_db", 24);
        }

        // STEP 25
        if ($cd_db <= 24) {
            $this->reloadControlStructure();
            $ilSetting->set("cd_db", 25);
        }

        // STEP 26
        if ($cd_db <= 25) {
            // add center to trainer
            $fields = array(
                'notes' => array(
                    'type' => 'clob'
                )
            );
            
            foreach ($fields as $f => $def) {
                $ilDB->addTableColumn("cd_trainer", $f, $def);
            }
            $ilSetting->set("cd_db", 26);
        }

        // STEP 27
        if ($cd_db <= 26) {
            // qm
            $fields = array(
                'qm' => array(
                    'type' => 'clob'
                ),
                'entry_date' => array(
                    'type' => 'timestamp'
                ),
                'fee' => array(
                    'type' => 'clob'
                )
            );
            
            foreach ($fields as $f => $def) {
                $ilDB->addTableColumn("cd_trainer", $f, $def);
            }
            $ilSetting->set("cd_db", 27);
        }
        
        // STEP 28
        if ($cd_db <= 27) {
            $ilDB->dropTableColumn("cd_trainer", "entry_date");
            
            $fields = array(
                'entry_date' => array(
                    'type' => 'date'
                )
            );
            
            foreach ($fields as $f => $def) {
                $ilDB->addTableColumn("cd_trainer", $f, $def);
            }
            $ilSetting->set("cd_db", 28);
        }
        
        // STEP 29
        if ($cd_db <= 28) {
            $fields = array(
                'interview' => array(
                    'type' => 'text',
                    'length' => 80,
                    'notnull' => false
                ),
                'competence_eval' => array(
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true,
                    'default' => 0
                ),
                'appearance' => array(
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true,
                    'default' => 0
                ),
                'overall_impression' => array(
                    'type' => 'clob'
                ),
                'supplier_eval' => array(
                    'type' => 'text',
                    'length' => 2,
                    'notnull' => false
                ),
                'add_arrangement' => array(
                    'type' => 'text',
                    'length' => 100,
                    'notnull' => false
                ),
                'gen_agreement_out' => array(
                    'type' => 'text',
                    'length' => 40,
                    'notnull' => false
                ),
                'gen_agreement_in' => array(
                    'type' => 'text',
                    'length' => 40,
                    'notnull' => false
                ),
                'train_guide_handout' => array(
                    'type' => 'text',
                    'length' => 40,
                    'notnull' => false
                )
            );

            foreach ($fields as $f => $def) {
                $ilDB->addTableColumn("cd_trainer", $f, $def);
            }
            $ilSetting->set("cd_db", 29);
        }

        // 4.4 start

        // STEP 30 ("new install step, should not run on migration")
        if ($cd_db <= 29) {
            if (!$ilDB->tableColumnExists('skl_self_eval_level', 'self_eval_id')) {
                $fields = array(
                    'self_eval_id' => array(
                        'type' => 'integer',
                        'length' => 4,
                        'notnull' => false
                    )
                );

                foreach ($fields as $f => $def) {
                    $ilDB->addTableColumn("skl_self_eval_level", $f, $def);
                }
            }

            $ilSetting->set("cd_db", 30);
        }

        // STEP 31 ("new install step, should not run on migration")
        if ($cd_db <= 30) {
            if ($ilDB->tableColumnExists('skl_self_eval_level', 'top_skill_id')) {
                // make db table like 4.1 cd skl_self_eval table
                $ilDB->dropPrimaryKey("skl_self_eval_level");
                $ilDB->addPrimaryKey("skl_self_eval_level", array("self_eval_id", "skill_id"));
            }

            $ilSetting->set("cd_db", 31);
        }

        // STEP 32 ("new install step, should not run on migration")
        // make table "look" the same as old cd 4.1 structure
        // templates are disabled in the competence administration
        // old class ilSkillSelfEvaluation is still used
        // ilPersonalSkill getUsageInfo is patched
        // 5.0: migrate all this to new structure
        if ($cd_db <= 31) {
            if ($ilDB->tableColumnExists('skl_self_eval_level', 'top_skill_id')) {
                $ilDB->dropTableColumn("skl_self_eval_level", "tref_id");
                $ilDB->dropTableColumn("skl_self_eval_level", "user_id");
                $ilDB->dropTableColumn("skl_self_eval_level", "last_update");
                $ilDB->dropTableColumn("skl_self_eval_level", "top_skill_id");
            }

            $ilSetting->set("cd_db", 32);
        }

        // 4.4 onwards

        // STEP 33
        if ($cd_db <= 32) {
            $this->reloadControlStructure();
            $ilSetting->set("cd_db", 33);
        }

        // STEP 34
        if ($cd_db <= 33) {
            // add center to trainer
            $fields = array(
                'cd_sel_country2' => array(
                    'type' => 'text',
                    'length' => 2,
                    'notnull' => false
                )
            );

            foreach ($fields as $f => $def) {
                $ilDB->addTableColumn("cd_trainer", $f, $def);
            }
            $ilSetting->set("cd_db", 34);
        }

        // STEP 35
        if ($cd_db <= 34) {
            $ilDB->modifyTableColumn("settings", "keyword", array(
                "type" => "text",
                "notnull" => "true",
                "length" => 60
            ));
            $ilSetting->set("cd_db", 35);
        }

        // STEP 36
        if ($cd_db <= 35) {
            $ilDB->modifyTableColumn(
                'cd_trainer_el',
                'id',
                array("type" => "integer", "length" => 4, "notnull" => true)
            );
            $ilDB->modifyTableColumn(
                'cd_trainer_fe',
                'id',
                array("type" => "integer", "length" => 4, "notnull" => true)
            );
            $ilDB->modifyTableColumn(
                'cd_trainer_il',
                'id',
                array("type" => "integer", "length" => 4, "notnull" => true)
            );
            $ilDB->modifyTableColumn(
                'cd_trainer_ls',
                'id',
                array("type" => "integer", "length" => 4, "notnull" => true)
            );
            $ilDB->modifyTableColumn(
                'cd_trainer_mtl',
                'id',
                array("type" => "integer", "length" => 4, "notnull" => true)
            );
            $ilDB->modifyTableColumn(
                'cd_trainer_tl',
                'id',
                array("type" => "integer", "length" => 4, "notnull" => true)
            );
            $ilDB->modifyTableColumn(
                'cd_trainer_tt',
                'id',
                array("type" => "integer", "length" => 4, "notnull" => true)
            );
            $ilSetting->set("cd_db", 36);
        }

        // STEP 37
        if ($cd_db <= 36) {
            // add center to trainer
            $fields = array(
                'former' => array(
                    'type' => 'integer',
                    'length' => 1,
                    'notnull' => true,
                    'default' => 0
                )
            );

            foreach ($fields as $f => $def) {
                $ilDB->addTableColumn("cd_trainer", $f, $def);
            }
            $ilSetting->set("cd_db", 37);
        }

        // STEP 38
        if ($cd_db <= 37) {
            $this->reloadControlStructure();
            $ilSetting->set("cd_db", 38);
        }

        // STEP 39
        if ($cd_db <= 38) {
            // teach type experience
            $fields = array(
                'user_id' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                ),
                'ts' => array(
                    'type' => 'timestamp'
                ),
                'changed_by' => array(
                    'type' => 'integer',
                    'length' => 4,
                    'notnull' => true
                )
            );

            $ilDB->createTable('cd_trainer_log', $fields);
            $ilDB->addIndex("cd_trainer_log", array("user_id"), "i1");

            $ilSetting->set("cd_db", 39);
        }


        // keep this line at the end of the method
        $this->finalProcessing();
    }

    public function reloadControlStructure()
    {
        $this->reload_control_structure = true;
    }

    public function finalProcessing()
    {
        global $ilDB, $ilClientIniFile;

        if ($this->reload_control_structure) {
            include_once("./Services/Database/classes/class.ilDBUpdate.php");
            //			chdir("./setup");
            include_once("./setup/classes/class.ilCtrlStructureReader.php");
            $GLOBALS["ilCtrlStructureReader"] = new ilCtrlStructureReader();
            $GLOBALS["ilCtrlStructureReader"]->setIniFile($ilClientIniFile);
            $GLOBALS["ilCtrlStructureReader"]->getStructure();
            $update = new ilDBUpdate($ilDB);
            $update->loadXMLInfo();
            //			chdir("..");
        }
    }
}
