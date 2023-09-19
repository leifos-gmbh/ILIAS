<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Needs analysis for DAF application class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class cdNeedsAnalysisDAF extends cdNeedsAnalysis
{
    protected static $daf_questions = array(
        1 => "daf_how_long_german",
        2 => "daf_why_learn_german",
        3 => "daf_wishes_course",
        4 => "daf_stay_germany",
        5 => "daf_hobbies",
        6 => "daf_free_time"
    );

    protected $daf_answer = array();

    /**
     * Get all questions
     *
     * @return array questions
     */
    public static function getQuestions($a_pl)
    {
        $q = array();
        foreach (self::$daf_questions as $k => $lv) {
            $q[$k] = $a_pl->txt($lv);
        }
        return $q;
    }


    /**
     * Set DAF answer
     *
     * @param int $i question id
     * @param string $a_val answer
     */
    public function setDAFAnswer($i, $a_val)
    {
        $this->daf_answer[$i] = $a_val;
    }

    /**
     * Get DAF answer
     *
     * @param int $i question id
     * @return string answer
     */
    public function getDAFAnswer($i)
    {
        return $this->daf_answer[$i];
    }

    /**
     * Read
     */
    public function read()
    {
        global $ilDB;

        parent::read();

        $set = $ilDB->query(
            "SELECT * FROM cd_needs_analysis_daf WHERE " .
            " na_id = " . $ilDB->quote($this->getId(), "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->setDafAnswer($rec["q_id"], $rec["answer"]);
        }
    }

    /**
     * Create needs analysis
     */
    public function create()
    {
        global $ilDB;

        parent::create();

        foreach (self::$daf_questions as $k => $q) {
            $ilDB->insert(
                "cd_needs_analysis_daf",
                array(
                    "na_id" => array("integer", $this->getId()),
                    "q_id" => array("integer", $k),
                    "answer" => array("clob", $this->getDAFAnswer($k))
                )
            );
        }
    }

    /**
     * Update needs analysis
     */
    public function update()
    {
        global $ilDB;

        parent::update();

        foreach (self::$daf_questions as $k => $q) {
            $set = $ilDB->query(
                "SELECT * FROM cd_needs_analysis_daf " .
                " WHERE na_id  = " . $ilDB->quote($this->getId(), "integer") .
                " AND q_id  = " . $ilDB->quote($k, "integer")
            );
            if ($rec = $ilDB->fetchAssoc($set)) {
                $ilDB->update(
                    "cd_needs_analysis_daf",
                    array(
                        "answer" => array("clob", $this->getDAFAnswer($k))
                    ),
                    array(
                        "na_id" => array("integer", $this->getId()),
                        "q_id" => array("integer", $k))
                );
            } else {
                $ilDB->insert(
                    "cd_needs_analysis_daf",
                    array(
                        "na_id" => array("integer", $this->getId()),
                        "q_id" => array("integer", $k),
                        "answer" => array("clob", $this->getDAFAnswer($k))
                    )
                );
            }
        }
    }

    /**
     * Get all needs analysis of a user
     */
    public static function getNeedsAnalysesOfUser($a_user_id, $a_one_per_lang = false)
    {
        global $ilDB;

        $set = $ilDB->query(
            "SELECT * FROM cd_needs_analysis " .
            " WHERE user_id = " . $ilDB->quote($a_user_id, "integer") .
            " ORDER BY ts DESC"
        );

        $needs_analysis = array();
        $langs = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $set2 = $ilDB->query(
                "SELECT * FROM cd_needs_analysis_daf " .
                " WHERE na_id = " . $ilDB->quote($rec["id"], "integer") .
                " ORDER BY q_id "
            );
            $daf = array();
            while ($rec2 = $ilDB->fetchAssoc($set2)) {
                $daf[$rec2["q_id"]] = $rec2;
            }
            $rec["daf"] = $daf;

            if (!$a_one_per_lang || !in_array($rec["course_lang"], $langs)) {
                $needs_analysis[] = $rec;
                $langs[] = $rec["course_lang"];
            }
        }
        return $needs_analysis;
    }
}
