<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Course Type application class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class cdCourseType
{
    /**
     * Constructor
     *
     * @param
     */
    public function __construct($a_pl, $a_id = 0)
    {
        $this->pl = $a_pl;
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
     * Set title
     *
     * @param	string	title
     */
    public function setTitle($a_val)
    {
        $this->title = $a_val;
    }

    /**
     * Get title
     *
     * @return	string	title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set language
     *
     * @param string $a_val language
     */
    public function setLanguage($a_val)
    {
        $this->lang = $a_val;
    }
    
    /**
     * Get language
     *
     * @return string language
     */
    public function getLanguage()
    {
        return $this->lang;
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
            "SELECT * FROM cd_course_type WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->setTitle($rec["title"]);
            $this->setLanguage($rec["lang"]);
        }
    }

    /**
     * Create course type
     */
    public function create()
    {
        global $ilDB;

        $this->setId($ilDB->nextId("cd_course_type"));

        $ilDB->manipulate("INSERT INTO cd_course_type " .
            "(id, title, lang) VALUES (" .
            $ilDB->quote($this->getId(), "integer") . "," .
            $ilDB->quote($this->getTitle(), "text") . "," .
            $ilDB->quote($this->getLanguage(), "text") .
            ")");
    }

    /**
     * Update course type
     */
    public function update()
    {
        global $ilDB;

        $ilDB->manipulate(
            "UPDATE cd_course_type SET " .
            " title = " . $ilDB->quote($this->getTitle(), "text") . ", " .
            " lang = " . $ilDB->quote($this->getLanguage(), "text") .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    /**
     * Delete
     */
    public function delete()
    {
        global $ilDB;

        $ilDB->manipulate(
            "DELETE FROM cd_course_type WHERE "
            . " id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    /**
     * Get all course types
     */
    public static function getAllCourseTypes($a_lang = "")
    {
        global $ilDB;

        $where = ($a_lang != "")
            ? " WHERE lang = " . $ilDB->quote($a_lang, "text")
            : "";

        $set = $ilDB->query(
            "SELECT * FROM cd_course_type " . $where . " ORDER BY title"
        );

        $course_type = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $course_type[] = $rec;
        }

        return $course_type;
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
            "SELECT $a_prop FROM cd_course_type WHERE " .
            " id = " . $ilDB->quote($a_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return $rec[$a_prop];
    }

    /**
     * Lookup title
     *
     * @param
     * @return
     */
    public static function lookupTitle($a_id)
    {
        return cdCourseType::lookupProperty($a_id, "title");
    }

    /**
     * Lookup languge
     *
     * @param
     * @return
     */
    public static function lookupLanguage($a_id)
    {
        return cdCourseType::lookupProperty($a_id, "lang");
    }
}
