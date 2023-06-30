<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * participant evaluation application class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 */
class cdParticipantEvaluation
{
    // id
    protected $id;

    /**
     * Constructor
     *
     * @param	integer	$a_id	participant evaluation id
     */
    public function __construct($a_id = 0)
    {
        if ($a_id > 0) {
            $this->setId($a_id);
            $this->read();
        }
    }

    /**
     * Set id
     *
     * @param	integer	$a_val	id
     */
    public function setId($a_val)
    {
        $this->id = $a_val;
    }

    /**
     * Get id
     *
     * @return	integer	id
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set participant id
     *
     * @param int $a_val participant id
     */
    public function setParticipantId($a_val)
    {
        $this->participant_id = $a_val;
    }
    
    /**
     * Get participant id
     *
     * @return int participant id
     */
    public function getParticipantId()
    {
        return $this->participant_id;
    }
    
    /**
     * Set trainer id
     *
     * @param int $a_val trainer id
     */
    public function setTrainerId($a_val)
    {
        $this->trainer_id = $a_val;
    }
    
    /**
     * Get trainer id
     *
     * @return int trainer id
     */
    public function getTrainerId()
    {
        return $this->trainer_id;
    }
    
    /**
     * Set course id
     *
     * @param int $a_val course id
     */
    public function setCourseId($a_val)
    {
        $this->course_id = $a_val;
    }
    
    /**
     * Get course id
     *
     * @return int course id
     */
    public function getCourseId()
    {
        return $this->course_id;
    }
    
    /**
     * Set after x lessons
     *
     * @param int $a_val after x lessons
     */
    public function setAfterXLessons($a_val)
    {
        $this->after_x_lessons = $a_val;
    }
    
    /**
     * Get after x lessons
     *
     * @return int after x lessons
     */
    public function getAfterXLessons()
    {
        return $this->after_x_lessons;
    }
    
    /**
     * Set starting cef
     *
     * @param string $a_val starting cef
     */
    public function setStartingCef($a_val)
    {
        $this->starting_cef = $a_val;
    }
    
    /**
     * Get starting cef
     *
     * @return string starting cef
     */
    public function getStartingCef()
    {
        return $this->starting_cef;
    }
    
    /**
     * Set current cef
     *
     * @param string $a_val current cef
     */
    public function setCurrentCef($a_val)
    {
        $this->current_cef = $a_val;
    }
    
    /**
     * Get current cef
     *
     * @return string current cef
     */
    public function getCurrentCef()
    {
        return $this->current_cef;
    }
    
    /**
     * Set skills
     *
     * @param string $a_val skills
     */
    public function setSkills($a_val)
    {
        $this->skills = $a_val;
    }
    
    /**
     * Get skills
     *
     * @return string skills
     */
    public function getSkills()
    {
        return $this->skills;
    }
    
    /**
     * Set test attendance
     *
     * @param bool $a_val test attendance
     */
    public function setTestAttendance($a_val)
    {
        $this->test_attendance = $a_val;
    }
    
    /**
     * Get test attendance
     *
     * @return bool test attendance
     */
    public function getTestAttendance()
    {
        return $this->test_attendance;
    }
    
    /**
     * Set test result
     *
     * @param string $a_val test result
     */
    public function setTestResult($a_val)
    {
        $this->test_result = $a_val;
    }
    
    /**
     * Get test result
     *
     * @return string test result
     */
    public function getTestResult()
    {
        return $this->test_result;
    }
    
    /**
     * Set stay in group
     *
     * @param bool $a_val stay in group
     */
    public function setStayInGroup($a_val)
    {
        $this->stay_in_group = $a_val;
    }
    
    /**
     * Get stay in group
     *
     * @return bool stay in group
     */
    public function getStayInGroup()
    {
        return $this->stay_in_group;
    }
    
    /**
     * Set recommended level
     *
     * @param string $a_val recommended level
     */
    public function setRecommendedLevel($a_val)
    {
        $this->recommended_level = $a_val;
    }
    
    /**
     * Get recommended level
     *
     * @return string recommended level
     */
    public function getRecommendedLevel()
    {
        return $this->recommended_level;
    }
    
    /**
     * Set creation date
     *
     * @param timestamp $a_val creation date
     */
    public function setCreationDate($a_val)
    {
        $this->creation_date = $a_val;
    }
    
    /**
     * Get creation date
     *
     * @return timestamp creation date
     */
    public function getCreationDate()
    {
        return $this->creation_date;
    }
    
    /**
     * Set update date
     *
     * @param timestamp $a_val update date
     */
    public function setUpdateDate($a_val)
    {
        $this->update_date = $a_val;
    }
    
    /**
     * Get update date
     *
     * @return timestamp update date
     */
    public function getUpdateDate()
    {
        return $this->update_date;
    }
    
    /**
     * Set test level
     *
     * @param string $a_val test level
     */
    public function setTestLevel($a_val)
    {
        $this->test_level = $a_val;
    }
    
    /**
     * Get test level
     *
     * @return string test level
     */
    public function getTestLevel()
    {
        return $this->test_level;
    }

    /**
     * Set published
     *
     * @param bool $a_val published
     */
    public function setPublished($a_val)
    {
        $this->published = $a_val;
    }

    /**
     * Get published
     *
     * @return bool published
     */
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * Read instance data from database
     */
    protected function read()
    {
        global $ilDB;

        $set = $ilDB->query(
            "SELECT * FROM cd_participant_eval WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            $this->setParticipantId($rec["participant_id"]);
            $this->setTrainerId($rec["trainer_id"]);
            $this->setCourseId($rec["course_id"]);
            $this->setAfterXLessons($rec["after_x_lessons"]);
            $this->setStartingCef($rec["starting_cef"]);
            $this->setCurrentCef($rec["current_cef"]);
            $this->setSkills(unserialize($rec["skills"]));
            $this->setTestAttendance($rec["test_attendance"]);
            $this->setTestResult($rec["test_result"]);
            $this->setStayInGroup($rec["stay_in_group"]);
            $this->setRecommendedLevel($rec["recommended_level"]);
            $this->setCreationDate($rec["creation_date"]);
            $this->setUpdateDate($rec["update_date"]);
            $this->setTestLevel($rec["test_level"]);
            $this->setPublished($rec["published"]);
        }
    }

    /**
     * Convert properties to DB fields
     *
     * @return array
     */
    protected function propertiesToFields()
    {
        $fields = array(
            "participant_id" => array("integer", $this->getParticipantId()),
            "trainer_id" => array("integer", (int) $this->getTrainerId()),
            "course_id" => array("integer", (int) $this->getCourseId()),
            "after_x_lessons" => array("integer", $this->getAfterXLessons()),
            "starting_cef" => array("text", $this->getStartingCef()),
            "current_cef" => array("text", $this->getCurrentCef()),
            "skills" => array("text", serialize($this->getSkills())),
            "test_attendance" => array("text", $this->getTestAttendance()),
            "test_result" => array("text", $this->getTestResult()),
            "stay_in_group" => array("integer", $this->getStayInGroup()),
            "recommended_level" => array("text", $this->getRecommendedLevel()),
            "test_level" => array("text", $this->getTestLevel()),
            "published" => array("integer", (int) $this->getPublished())
            );
        return $fields;
    }

    /**
     * Create participant evaluation
     */
    public function create()
    {
        global $ilDB;

        $this->setId($ilDB->nextId("cd_participant_eval"));
        $id = $this->getId();

        $fields = $this->propertiesToFields();
        $fields["id"] = array("integer", $id);
        $fields["creation_date"] = array("timestamp", ilUtil::now());
        $fields["update_date"] = array("timestamp", ilUtil::now());

        $ilDB->insert("cd_participant_eval", $fields);
    }

    /**
     * Update participant evaluation
     */
    public function update()
    {
        global $ilDB;

        $id = $this->getId();
        if (!$id) {
            return false;
        }

        $fields = $this->propertiesToFields();
        $fields["update_date"] = array("timestamp", ilUtil::now());

        $ilDB->update(
            "cd_participant_eval",
            $fields,
            array("id" => array("integer", $id))
        );

        return true;
    }

    /**
     * Delete participant evaluation
     */
    public function delete()
    {
        global $ilDB;

        $ilDB->manipulate(
            "DELETE FROM cd_participant_eval WHERE "
            . " id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    /**
     * Get all participant evaluations
     *
     * @return	array	participant evaluation data
     */
    public static function getAllParticipantEvaluations($a_filter = array())
    {
        global $ilDB;

        $where = "";
        
        $and = " WHERE ";
        if (isset($a_filter["user_id"])) {
            $where .= $and . " participant_id = " . $ilDB->quote($a_filter["user_id"], "integer");
            $and = " AND ";
        }
        if (isset($a_filter["course_id"])) {
            $where .= $and . " course_id = " . $ilDB->quote($a_filter["course_id"], "integer");
            $and = " AND ";
        }
        
        $set = $ilDB->query("SELECT * FROM cd_participant_eval" . $where);

        $items = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $items[] = $rec;
        }

        return $items;
    }

    /**
     * Get all needs analysis of a user
     */
    public static function getPEsOfUser($a_user_id, $a_course_id)
    {
        return self::getAllParticipantEvaluations(array("user_id" => $a_user_id,
            "course_id" => $a_course_id));
    }

    /**
     * Get all needs analysis of a course
     */
    public static function getPEsOfCourse($a_course_id)
    {
        return self::getAllParticipantEvaluations(array("course_id" => $a_course_id));
    }

    
    /**
     * Lookup property
     *
     * @param	integer	$a_id	participant evaluation id
     * @param	string	$a_prop	property
     *
     * @return	mixed	property value
     */
    protected static function lookupProperty($a_id, $a_prop)
    {
        global $ilDB;

        $set = $ilDB->query(
            "SELECT $a_prop FROM cd_participant_eval WHERE " .
            " id = " . $ilDB->quote($a_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return $rec[$a_prop];
    }

    /**
     * Lookup participant id
     *
     * @param	integer	participant evaluation id
     *
     * @return	int participant id
     */
    public static function lookupParticipantId($a_id)
    {
        return ilParticipantEvaluation::lookupProperty($a_id, "participant_id");
    }

    /**
     * Count evals
     *
     * @param
     * @return
     */
    public static function countEvals($a_user_id, $a_course_id)
    {
        global $ilDB;
        
        $set = $ilDB->query(
            "SELECT count(id) cnt FROM cd_participant_eval " .
            " WHERE participant_id = " . $ilDB->quote($a_user_id, "integer") .
            " AND course_id = " . $ilDB->quote($a_course_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return (int) $rec["cnt"];
    }
    
    /**
     * Get eval notifications for courses
     *
     * @param
     * @return
     */
    public static function getEvalNotificationsForCourses($a_course)
    {
        global $rbacsystem, $ilDB;
        
        $members = array();
        if (is_array($a_course)) {
            // get all activation data of the courses
            $obj_ids = array();
            $activation = array();
            foreach ($a_course as $crs) {
                $obj_ids[$crs["obj_id"]] = $crs["obj_id"];
            }
            
            $now = time();
            
            // notify one day before
            $buffer = 1 * 60 * 60 * 24;
            
            if (count($obj_ids) > 0) {
                $set = $ilDB->query("SELECT obj_id, activation_type, activation_start, activation_end FROM crs_settings " .
                    " WHERE " . $ilDB->in("obj_id", $obj_ids, false, "integer"));
                while ($rec = $ilDB->fetchAssoc($set)) {
                    if ($rec["activation_type"] == 2) {
                        $rec["duration"] = $rec["activation_end"] - $rec["activation_start"];
                        $rec["mid"] = (int) ($rec["activation_start"] + round($rec["duration"] / 2));
                        $target_evals = 0;
                        if ($now > $rec["activation_end"] - $buffer) {
                            // to evals needed, if the end is near
                            $target_evals = 2;
                        } elseif ($now > $rec["mid"] - $buffer) {
                            // only one evaluation needed at the middle of the course
                            $target_evals = 1;
                        }
                        $rec["target_evals"] = $target_evals;
                    }
                    $activation[$rec["obj_id"]] = $rec;
                }
            }
            //var_dump($activation);
            $ref_ids = array();
            $obj_ids = array();
            $roles = array();
            reset($a_course);
            foreach ($a_course as $crs) {
                // check start/end date of course
                if ($activation[$crs["obj_id"]]["activation_type"] != 2) {
                    continue;
                }
                
                // check write permission
                if ($rbacsystem->checkAccess("write", $crs["ref_id"]) ||
                    $rbacsystem->checkAccess("edit_learning_progress", $crs["ref_id"])) {
                    $ref_ids[$crs["ref_id"]] = $crs["obj_id"];
                    $obj_ids[$crs["obj_id"]] = $crs["obj_id"];
                    $roles[] = "il_crs_member_" . $crs["ref_id"];
                }
            }
            // get a members for obj_id array
            
            if (count($ref_ids)) {
                $set = $ilDB->query("SELECT od.title, ua.usr_id FROM object_data od JOIN rbac_ua ua ON (od.obj_id = ua.rol_id) " .
                    "WHERE " . $ilDB->in("od.title", $roles, false, "text"));
                    
                while ($rec = $ilDB->fetchAssoc($set)) {
                    $ref_id = substr($rec["title"], 14);
                    $obj_id = $ref_ids[$ref_id];
                    $members[$obj_id]["crs_id"] = $obj_id;
                    $members[$obj_id]["crs_ref_id"] = $ref_id;
                    $members[$obj_id]["crs_activation"] = $activation[$obj_id];
                    $members[$obj_id]["users"][$rec["usr_id"]] = array("usr_id" => $rec["usr_id"]);
                }
            }
            
            //var_dump($activation);
            if (count($members) > 0) {
                // count notifications for courses
                $set = $ilDB->query("SELECT course_id, participant_id, count(id) cnt FROM cd_participant_eval " .
                    " WHERE " . $ilDB->in("course_id", $obj_ids, false, "integer") .
                    " GROUP BY participant_id, course_id");
                while ($rec = $ilDB->fetchAssoc($set)) {
                    if ($rec["cnt"] < $activation[$rec["course_id"]]["target_evals"]) {
                        $members[$rec["course_id"]]["users"][$rec["participant_id"]]["cnt_eval"] = $rec["cnt"];
                    } else {
                        unset($members[$rec["course_id"]]["users"][$rec["participant_id"]]);
                    }
                }
            }
        }
        
        // remove all courses without users
        $result = array();
        foreach ($members as $k => $m) {
            if (count($m["users"]) > 0) {
                $result[$k] = $m;
            }
        }

        return $result;
    }

    /**
     * Get skill scale values
     *
     * @param
     * @return
     */
    public static function getSkillScaleValues($a_pl)
    {
        return array(
            1 => $a_pl->txt("needs_work"),
            2 => $a_pl->txt("satisfactory"),
            3 => $a_pl->txt("good"),
            4 => $a_pl->txt("very_good"),
            5 => $a_pl->txt("excellent")
        );
    }
}
