<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Entry level test application class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class cdEntryLevelTest
{
    /**
     * Constructor
     *
     * @param
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
     * @param	integer	id
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
     * Read
     *
     * @param
     * @return
     */
    public function read()
    {
        global $ilDB;

        $set = $ilDB->query(
            "SELECT * FROM cd_ext_test WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $rec["ext_level"] = self::calcExtLevel($rec["points"]);
            $this->setData($rec);
        }
    }

    /**
     * Set data
     *
     * @param	array	$a_val	data
     */
    public function setData($a_val)
    {
        $this->data = $a_val;
    }

    /**
     * Get data
     *
     * @return	array	data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Create entry level test
     */
    /*
        function create()
        {
            global $ilDB;

            $this->setId($ilDB->nextId("cd_ext_test"));

            $ilDB->manipulate("INSERT INTO cd_ext_test ".
            "(id, title) VALUES (".
            $ilDB->quote($this->getId(), "integer").",".
            $ilDB->quote($this->getTitle(), "text").
            ")");
        }
     */

    /**
     * Update entry level test
     */
    /*
        function update()
        {
            global $ilDB;

            $ilDB->manipulate("UPDATE cd_ext_test SET ".
            " title = ".$ilDB->quote($this->getTitle(), "text").
            " WHERE id = ".$ilDB->quote($this->getId(), "integer")
            );
        }
     */

    /**
     * Delete entry level test
     */
    public function delete()
    {
        global $ilDB;

        $ilDB->manipulate(
            "DELETE FROM cd_ext_test WHERE "
            . " id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    /**
     * Get all entry level tests of user
     */
    public static function getEntryLevelTestsOfUser($a_user_id, $a_one_per_lang = false)
    {
        global $ilDB;

        $set = $ilDB->query("SELECT * FROM cd_ext_test "
            . " WHERE user_id = " . $ilDB->quote($a_user_id, "integer") .
            " ORDER BY ts DESC");

        $entry_level_test = array();
        $langs = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            if (!$a_one_per_lang || !in_array($rec["target_lang"], $langs)) {
                $rec["ext_level"] = self::calcExtLevel($rec["points"]);
                $entry_level_test[] = $rec;
                $langs[] = $rec["target_lang"];
            }
        }

        return $entry_level_test;
    }

    /**
     * Lookup property
     *
     * @param	int		level id
     * @return	mixed	property value
     */
    protected static function lookupProperty($a_id, $a_prop)
    {
        global $ilDB;

        $set = $ilDB->query(
            "SELECT $a_prop FROM cd_ext_test WHERE " .
            " id = " . $ilDB->quote($a_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return $rec[$a_prop];
    }

    /**
     * Lookup user id
     *
     * @param
     * @return
     */
    public static function lookupUserId($a_id)
    {
        return cdEntryLevelTest::lookupProperty($a_id, "user_id");
    }

    /**
     * Lookup language
     *
     * @param
     * @return
     */
    public static function lookupLanguage($a_id)
    {
        return cdEntryLevelTest::lookupProperty($a_id, "target_lang");
    }

    /**
     * Lookup creation date
     *
     * @param
     * @return
     */
    public static function lookupCreationDate($a_id)
    {
        return cdEntryLevelTest::lookupProperty($a_id, "ts");
    }

    /**
     * Get available tests
     *
     * @param
     * @return
     */
    public static function getAvailableTests()
    {
        if (cdUtil::isDAF()) {
            return array(
                "de" => array("de", "en", "fr", "it", "es")
            );
        }

        return array(
            "de" => array("de", "en", "fr", "it", "es"),
            "en" => array("de", "en"),
            "fr" => array("de", "en"),
            "it" => array("de", "en"),
            "es" => array("de", "en")
        );
    }

    /**
     * Write test session
     *
     * @param
     * @return
     */
    public static function writeTestSession()
    {
        global $ilUser, $ilDB;

        $ilDB->manipulate(
            "DELETE FROM cd_ext_test_sess WHERE "
            . " user_id = " . $ilDB->quote($ilUser->getId(), "integer")
        );
        
        $set = $ilDB->query(
            "SELECT * FROM usr_session WHERE " .
            " session_id = " . $ilDB->quote(session_id(), "text")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            $ilDB->insert("cd_ext_test_sess", array(
                "session_id" => array("text", session_id()),
                "user_id" => array("integer", $ilUser->getId()),
                "ts" => array("timestamp", ilUtil::now()),
                "sess_data" => array("clob", $rec["data"]),
            ));
        }
    }
    
    /**
     * Calculate Extended Level
     *
     * @param int points poitns as delivered by the external test
     * @return string extended level representations
     */
    public static function calcExtLevel($a_points)
    {
        if ($a_points * 10 >= 99 && $a_points * 10 <= 100) {
            $points = "C2";
        } elseif ($a_points * 10 >= 95 && $a_points * 10 <= 98) {
            $points = "C1/2";
        } elseif ($a_points * 10 >= 85 && $a_points * 10 <= 94) {
            $points = "C1/1";
        } elseif ($a_points * 10 >= 75 && $a_points * 10 <= 84) {
            $points = "B2/3";
        } elseif ($a_points * 10 >= 65 && $a_points * 10 <= 74) {
            $points = "B2/2";
        } elseif ($a_points * 10 >= 55 && $a_points * 10 <= 64) {
            $points = "B2/1";
        } elseif ($a_points * 10 >= 45 && $a_points * 10 <= 54) {
            $points = "B1/2";
        } elseif ($a_points * 10 >= 35 && $a_points * 10 <= 44) {
            $points = "B1/1";
        } elseif ($a_points * 10 >= 25 && $a_points * 10 <= 34) {
            $points = "A2";
        } elseif ($a_points * 10 >= 15 && $a_points * 10 <= 25) {
            $points = "A1";
        } else {
            $points = "0/0";
        }
        return $points;
    }

    /**
     * Get last test dates for users of company
     *
     * @param
     * @return
     */
    public static function getLastTestDatesForCompany($a_company_id)
    {
        global $ilDB;
        
        $set = $ilDB->query(
            "SELECT MAX(cd_ext_test.ts) ts, cd_ext_test.user_id FROM usr_data " .
            " JOIN cd_ext_test ON (usr_data.usr_id = cd_ext_test.user_id) " .
            " WHERE company_id = " . $ilDB->quote($a_company_id, "integer") .
            " GROUP BY cd_ext_test.user_id, ts " .
            " ORDER BY cd_ext_test.ts DESC "
        );
        $tests = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $tests[$rec["user_id"]] = $rec["ts"];
        }
        return $tests;
    }
}
