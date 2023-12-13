<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *  Participation Confirmation application class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class cdParticipationConfirmation
{
    /**
     * Constructor
     *
     * @param
     * @return
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
     * @param int $a_val id
     */
    public function setId($a_val)
    {
        $this->id = $a_val;
    }
    
    /**
     * Get id
     *
     * @return int id
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Set title
     *
     * @param string $a_val title
     */
    public function setTitle($a_val)
    {
        $this->title = $a_val;
    }
    
    /**
     * Get title
     *
     * @return string title
     */
    public function getTitle()
    {
        return $this->title;
    }
    
    /**
     * Set filename
     *
     * @param string $a_val file name
     */
    public function setFilename($a_val)
    {
        $this->filename = $a_val;
    }
    
    /**
     * Get filename
     *
     * @return string file name
     */
    public function getFilename()
    {
        return $this->filename;
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
            "SELECT * FROM cd_part_conf_temp " .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->setTitle($rec["title"]);
            $this->setFilename($rec["filename"]);
        }
    }
    
    /**
     * Save new confirmation
     *
     * @param
     * @return
     */
    public function save()
    {
        global $ilDB;
        
        $this->setId($ilDB->nextId("cd_part_conf_temp"));
        $ilDB->manipulate("INSERT INTO cd_part_conf_temp " .
            "(id, title, filename) VALUES (" .
            $ilDB->quote($this->getId(), "integer") . "," .
            $ilDB->quote($this->getTitle(), "text") . "," .
            $ilDB->quote($this->getFilename(), "text") .
            ")");
    }
    
    /**
     * Update confirmatio
     *
     * @param
     * @return
     */
    public function update()
    {
        global $ilDB;
        
        $ilDB->manipulate(
            "UPDATE cd_part_conf_temp SET " .
            " title = " . $ilDB->quote($this->getTitle(), "text") . "," .
            " filename = " . $ilDB->quote($this->getFilename(), "text") .
            " WHERE id = " . $ilDB->quote($this->getId(), "integer")
        );
    }
    
    /**
     * Delete
     *
     * @param
     * @return
     */
    public function delete()
    {
        global $ilDB;
        
        $ilDB->manipulate(
            "DELETE FROM cd_part_conf_temp WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer")
        );
    }
    
    
    /**
     * Get file directory for
     *
     * @param
     * @return
     */
    public static function getDirectory()
    {
        return ILIAS_DATA_DIR . "/cd/part_conf_templates";
    }
    
    /**
     * Create directory
     *
     * @param
     * @return
     */
    public static function createDirectory()
    {
        ilUtil::makeDirParents(self::getDirectory());
    }
    
    /**
     * Add uploaded file
     *
     * @param array $a_file files array
     */
    public static function addUploadedFile($a_file)
    {
        self::createDirectory();
        ilUtil::moveUploadedFile(
            $a_file["tmp_name"],
            $a_file["name"],
            self::getDirectory() . "/" . $a_file["name"]
        );
    }
    
    /**
     * Get all confirmation files
     *
     * @param
     * @return
     */
    public static function getAllConfirmationFiles()
    {
        global $ilDB;
        
        $set = $ilDB->query("SELECT * FROM cd_part_conf_temp " .
            " ORDER BY title");
        $files = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            if (is_file(self::getDirectory() . "/" . $rec["filename"])) {
                $files[$rec["id"]] = array("id" => $rec["id"],
                    "filename" => $rec["filename"], "title" => $rec["title"]);
            }
        }

        return $files;
    }
    
    
    /**
     * Download participation confirmation zip file
     *
     * @param
     * @return
     */
    public static function downloadConfirmationFile($a_pl, $a_ref_id, $a_conf, $a_placeholders, $date)
    {
        // get confirmation template
        $a_pl->includeClass("class.cdParticipationConfirmation.php");
        $conf = new cdParticipationConfirmation($a_conf);
        
        // create temporary directory
        $tmpdir = ilUtil::ilTempnam();
        ilUtil::makeDir($tmpdir);
        
        // init rtf replacer
        $rtf = new ilRtfReplacer();
        
        // get course object
        $course = new ilObjCourse($a_ref_id);
        
        $parts = ilCourseParticipants::_getInstanceByObjId(ilObject::_lookupObjId($a_ref_id))->getMembers();

        $a_pl->includeClass("class.cdCourseType.php");
        
        foreach ($parts as $user_id) {
            $name = ilObjUser::_lookupName($user_id);
            $course_type_id = $course->getCourseType();
            $course_type = cdCourseType::lookupTitle($course_type_id);
            $date = new ilDate($date->get(IL_CAL_DATETIME), IL_CAL_DATETIME);
            ilDatePresentation::setUseRelativeDates(false);
            $a_placeholders["date"] = ilDatePresentation::formatDate($date);
            $placeholders = array(
                "Vorname" => $name["firstname"],
                "Nachname" => $name["lastname"],
                "Trainingsart" => $course_type,
                "KursVon" => $a_placeholders["course_from"],
                "KursBis" => $a_placeholders["course_until"],
                "Ort" => $a_placeholders["city"],
                "Datum" => $a_placeholders["date"],
                "NameUnterschrift" => $a_placeholders["name_signature"],
                "Funktion" => $a_placeholders["function"]
                );
            $input_file = self::getDirectory() . "/" . $conf->getFilename();
            $output_file = $tmpdir . "/" . ilUtil::getASCIIFilename(
                $name["lastname"] . "_" . $name["firstname"] . "_" . $user_id . ".rtf"
            );
            $rtf->processFile($placeholders, $input_file, $output_file);
        }
        
        // zip the stuff
        ilUtil::zip($tmpdir, "Teilnahme.zip", true);
        
        // deliver the zip
        ilUtil::deliverFile(
            $tmpdir . "/Teilnahme.zip",
            "Teilnahme.zip",
            "",
            false,
            false,
            false
        );
        ilUtil::delDir($tmpdir);
        exit;
    }
    
    /**
     * Lookup
     *
     * @param
     * @return
     */
    public static function lookup($a_id, $a_field)
    {
        global $ilDB;
        
        $set = $ilDB->query(
            "SELECT " . $a_field . " FROM cd_part_conf_temp " .
            " WHERE id = " . $ilDB->quote($a_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return $rec[$a_field];
    }
    
    
    /**
     * Lookup title
     *
     * @param int $a_id ID
     * @return string title
     */
    public static function lookupTitle($a_id)
    {
        return self::lookup($a_id, "title");
    }
    
    /**
     * Deliver current confirmation template file
     *
     * @param
     * @return
     */
    public function deliver()
    {
        ilUtil::deliverFile(self::getDirectory() . "/" . $this->getFilename(), $this->getFilename());
    }
}
