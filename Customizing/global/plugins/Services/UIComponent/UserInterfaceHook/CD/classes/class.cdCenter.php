<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Center application class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class cdCenter
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
     * Set category ref id
     *
     * @param	integer	$a_val	category ref id
     */
    public function setCategory($a_val)
    {
        $this->category = $a_val;
    }

    /**
     * Get category ref id
     *
     * @return	integer	category ref id
     */
    public function getCategory()
    {
        return $this->category;
    }
    
    /**
     * Set email
     *
     * @param string $a_val email
     */
    public function setEmail($a_val)
    {
        $this->email = $a_val;
    }
    
    /**
     * Get email
     *
     * @return string email
     */
    public function getEmail()
    {
        return $this->email;
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
            "SELECT * FROM cd_center WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->setTitle($rec["title"]);
            $this->setCategory($rec["category"]);
            $this->setEmail($rec["email"]);
        }
    }

    /**
     * Create center
     */
    public function create()
    {
        global $ilDB;

        $this->setId($ilDB->nextId("cd_center"));

        $ilDB->manipulate("INSERT INTO cd_center " .
            "(id, title, category, email) VALUES (" .
            $ilDB->quote($this->getId(), "integer") . "," .
            $ilDB->quote($this->getTitle(), "text") . "," .
            $ilDB->quote($this->getCategory(), "integer") . "," .
            $ilDB->quote($this->getEmail(), "text") .
            ")");
    }

    /**
     * Update center
     */
    public function update()
    {
        global $ilDB;

        $ilDB->manipulate(
            "UPDATE cd_center SET " .
            " title = " . $ilDB->quote($this->getTitle(), "text") . ", " .
            " category = " . $ilDB->quote($this->getCategory(), "text") . ", " .
            " email = " . $ilDB->quote($this->getEmail(), "text") .
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
            "DELETE FROM cd_center WHERE "
            . " id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    /**
     * Get all centers
     */
    public static function getAllCenters()
    {
        global $ilDB;

        $set = $ilDB->query(
            "SELECT * FROM cd_center ORDER BY title"
        );

        $center = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $center[] = $rec;
        }

        return $center;
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
            "SELECT $a_prop FROM cd_center WHERE " .
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
        return cdCenter::lookupProperty($a_id, "title");
    }

    /**
     * Lookup title
     *
     * @param
     * @return
     */
    public static function lookupCategory($a_id)
    {
        return cdCenter::lookupProperty($a_id, "category");
    }

    /**
     * Lookup email
     *
     * @param int $a_id ID
     * @return string email
     */
    public static function lookupEmail($a_id)
    {
        return cdCenter::lookupProperty($a_id, "email");
    }

    /**
     * exists
     *
     * @param
     * @return
     */
    public static function exists($a_id)
    {
        global $ilDB;
        
        $set = $ilDB->query(
            "SELECT * FROM cd_center WHERE " .
            " id = " . $ilDB->quote($a_id, "integer")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            return true;
        }
        return false;
    }
}
