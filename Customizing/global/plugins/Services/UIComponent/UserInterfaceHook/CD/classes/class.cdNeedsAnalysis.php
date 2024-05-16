<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Needs analysis application class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class cdNeedsAnalysis
{
    public static $lang_levels = array("A1", "A2", "B1", "B2", "C1", "C2");

    // never change IDs for a category!!!
    // only IDs are stored in the database
    public static $talk_understand = array(
        "10" => "present_projects",
        "20" => "conduct_negotiations",
        "30" => "participate_in_meetings",
        "40" => "make_phone_calls",
        "50" => "phone_questions",
        ##"60" => "service_phone",
        "70" => "typewriting",
        "80" => "offers_negotiate",
        "90" => "business_hotel",
        "100" => "visitors_supervise"
        );
    public static $write_read = array(
        "10" => "write_letters_or_emails",
        "20" => "write_faxes_or_memos",
        "30" => "write_reports",
        "40" => "read_technical_specialized_text",
        "50" => "read_reports",
        ##"60" => "read_financial_documents"
        );
    public static $technical_lang = array(
        "10" => "law",
        "20" => "medicin",
        "30" => "it",
        "40" => "technology",
        "50" => "finance",
        "60" => "human_resources",
        "70" => "marketing_and_sales",
        );

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
     * Get languages
     */
    public static function getLanguages()
    {
        $pl = new ilCDPlugin();
        $pl->includeClass("class.cdLang.php");
        return cdLang::getLanguages();
        return self::$langs;
    }
    
    /**
     * Get language levels
     */
    public static function getLanguageLevels()
    {
        return self::$lang_levels;
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
     * Set user id
     *
     * @param	string	user id
     */
    public function setUserId($a_val)
    {
        $this->user_id = $a_val;
    }

    /**
     * Get user id
     *
     * @return	string	user id
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set created
     *
     * @param	string	created
     */
    public function setCreated($a_val)
    {
        $this->created = $a_val;
    }

    /**
     * Get created
     *
     * @return	string	created
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set last update
     *
     * @param	string	last update
     */
    public function setLastUpdate($a_val)
    {
        $this->last_update = $a_val;
    }

    /**
     * Get last update
     *
     * @return	string	last update
     */
    public function getLastUpdate()
    {
        return $this->last_update;
    }
    
    /**
     * Set course language
     *
     * @param	string	course language
     */
    public function setCourseLanguage($a_val)
    {
        $this->course_language = $a_val;
    }

    /**
     * Get course language
     *
     * @return	string	course language
     */
    public function getCourseLanguage()
    {
        return $this->course_language;
    }

    /**
     * Set other languages (selected)
     *
     * @param	string	other languages (selected)
     */
    public function setOtherLangSel($a_val)
    {
        $this->other_lang_sel = $a_val;
    }

    /**
     * Get other languages (selected)
     *
     * @return	string	other languages (selected)
     */
    public function getOtherLangSel()
    {
        return $this->other_lang_sel;
    }

    /**
     * Set other languages (free)
     *
     * @param	string	other languages (free)
     */
    public function setOtherLangFree($a_val)
    {
        $this->other_lang_free = $a_val;
    }

    /**
     * Get other languages (free)
     *
     * @return	string	other languages (free)
     */
    public function getOtherLangFree()
    {
        return $this->other_lang_free;
    }

    /**
     * Set last course
     *
     * @param	string	last course
     */
    public function setLastCourse($a_val)
    {
        $this->last_course = $a_val;
    }

    /**
     * Get last course
     *
     * @return	string	last course
     */
    public function getLastCourse()
    {
        return $this->last_course;
    }

    /**
     * Set lang level
     *
     * @param	string	lang level
     */
    public function setLangLevel($a_val)
    {
        $this->lang_level = $a_val;
    }

    /**
     * Get lang level
     *
     * @return	string	lang level
     */
    public function getLangLevel()
    {
        return $this->lang_level;
    }

    /**
     * Set talk/understanding
     *
     * @param	string	talk/understanding
     */
    public function setTalkUnderstanding($a_val)
    {
        $this->talk_understanding = $a_val;
    }

    /**
     * Get talk/understanding
     *
     * @return	string	talk/understanding
     */
    public function getTalkUnderstanding()
    {
        return $this->talk_understanding;
    }

    /**
     * Set write/read
     *
     * @param	string	write/read
     */
    public function setWriteRead($a_val)
    {
        $this->write_read = $a_val;
    }

    /**
     * Get write/read
     *
     * @return	string	write/read
     */
    public function getWriteRead()
    {
        return $this->write_read;
    }

    /**
     * Set technical language
     *
     * @param	string	technical language
     */
    public function setTechnicalLanguage($a_val)
    {
        $this->technical_lang = $a_val;
    }

    /**
     * Get technical language
     *
     * @return	string	technical language
     */
    public function getTechnicalLanguage()
    {
        return $this->technical_lang;
    }

    /**
     * Set technical language (free)
     *
     * @param	string	technical language (free)
     */
    public function setTechnicalLanguageFree($a_val)
    {
        $this->technical_language_free = $a_val;
    }

    /**
     * Get technical language (free)
     *
     * @return	string	technical language (free)
     */
    public function getTechnicalLanguageFree()
    {
        return $this->technical_language_free;
    }

    /**
     * Read
     */
    public function read()
    {
        global $ilDB;

        $set = $ilDB->query(
            "SELECT * FROM cd_needs_analysis WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->setUserId($rec["user_id"]);
            $this->setCreated($rec["ts"]);
            $this->setLastUpdate($rec["last_update"]);
            $this->setCourseLanguage($rec["course_lang"]);
            $this->setOtherLangSel($rec["olang_sel"]);
            $this->setOtherLangFree($rec["olang_free"]);
            $this->setLastCourse($rec["last_course"]);
            $this->setLangLevel($rec["lang_level"]);
            $this->setTalkUnderstanding($rec["talk_understanding"]);
            $this->setWriteRead($rec["write_read"]);
            $this->setTechnicalLanguage($rec["tech_lang_sel"]);
            $this->setTechnicalLanguageFree($rec["tech_lang_free"]);
        }
    }

    /**
     * Create needs analysis
     */
    public function create()
    {
        global $ilDB;

        $this->setId($ilDB->nextId("cd_needs_analysis"));

        $ilDB->manipulate("INSERT INTO cd_needs_analysis " .
            "(id, user_id, ts, last_update, course_lang, olang_sel, olang_free, last_course," .
            "lang_level, talk_understanding, write_read, tech_lang_sel, tech_lang_free) VALUES (" .
            $ilDB->quote($this->getId(), "integer") . "," .
            $ilDB->quote($this->getUserId(), "integer") . "," .
            $ilDB->now() . "," .
            $ilDB->now() . "," .
            $ilDB->quote($this->getCourseLanguage(), "text") . "," .
            $ilDB->quote($this->getOtherLangSel(), "text") . "," .
            $ilDB->quote($this->getOtherLangFree(), "text") . "," .
            $ilDB->quote($this->getLastCourse(), "text") . "," .
            $ilDB->quote($this->getLangLevel(), "text") . "," .
            $ilDB->quote($this->getTalkUnderstanding(), "text") . "," .
            $ilDB->quote($this->getWriteRead(), "text") . "," .
            $ilDB->quote($this->getTechnicalLanguage(), "text") . "," .
            $ilDB->quote($this->getTechnicalLanguageFree(), "text") .
            ")");
    }

    /**
     * Update needs analysis
     */
    public function update()
    {
        global $ilDB;

        $ilDB->manipulate(
            $q = "UPDATE cd_needs_analysis SET " .
            " user_id = " . $ilDB->quote($this->getUserId(), "text") . "," .
            " last_update = " . $ilDB->now() . "," .
            " course_lang = " . $ilDB->quote($this->getCourseLanguage(), "text") . "," .
            " olang_sel = " . $ilDB->quote($this->getOtherLangSel(), "text") . "," .
            " olang_free = " . $ilDB->quote($this->getOtherLangFree(), "text") . "," .
            " last_course = " . $ilDB->quote($this->getLastCourse(), "text") . "," .
            " lang_level = " . $ilDB->quote($this->getLangLevel(), "text") . "," .
            " talk_understanding = " . $ilDB->quote($this->getTalkUnderstanding(), "text") . "," .
            " write_read = " . $ilDB->quote($this->getWriteRead(), "text") . "," .
            " tech_lang_sel = " . $ilDB->quote($this->getTechnicalLanguage(), "text") . "," .
            " tech_lang_free = " . $ilDB->quote($this->getTechnicalLanguageFree(), "text") .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    /**
     * Delete needs analysis
     */
    public function delete()
    {
        global $ilDB;

        $ilDB->manipulate(
            "DELETE FROM cd_needs_analysis WHERE "
            . " id = " . $ilDB->quote($this->getId(), "integer")
        );
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
            if (!$a_one_per_lang || !in_array($rec["course_lang"], $langs)) {
                $needs_analysis[] = $rec;
                $langs[] = $rec["course_lang"];
            }
        }
        return $needs_analysis;
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
            "SELECT $a_prop FROM cd_needs_analysis WHERE " .
        " id = " . $ilDB->quote($a_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return $rec[$a_prop];
    }

    /**
     * Lookup language
     *
     * @param	int		level id
     * @return	mixed	property value
     */
    public static function lookupLanguage($a_id)
    {
        return cdNeedsAnalysis::lookupProperty($a_id, "course_lang");
    }

    /**
     * Lookup create date
     *
     * @param	int		level id
     * @return	mixed	property value
     */
    public static function lookupCreated($a_id)
    {
        return cdNeedsAnalysis::lookupProperty($a_id, "ts");
    }
}
