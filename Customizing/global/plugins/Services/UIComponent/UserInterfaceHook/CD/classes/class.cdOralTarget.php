<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *  Oral and target marks
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$ 
 */
class cdOralTarget
{
	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	function __construct($a_id = 0)
	{
		if ($a_id > 0)
		{
			$this->setId((int) $a_id);
			$this->read();
		}
	}

	/**
	 * Set id
	 *
	 * @param integer $a_val id	
	 */
	function setId($a_val)
	{
		$this->id = $a_val;
	}
	
	/**
	 * Get id
	 *
	 * @return integer id
	 */
	function getId()
	{
		return $this->id;
	}
	
	/**
	 * Set user id
	 *
	 * @param integer $a_val user id
	 */
	function setUserId($a_val)
	{
		$this->user_id = $a_val;
	}

	/**
	 * Get user id
	 *
	 * @return integer user id
	 */
	function getUserId()
	{
		return $this->user_id;
	}

	/**
	 * Set Language
	 *
	 * @param string $a_val language
	 */
	function setLanguage($a_val)
	{
		$this->lang = $a_val;
	}

	/**
	 * Get Language
	 *
	 * @return string language
	 */
	function getLanguage()
	{
		return $this->lang;
	}

	/**
	 * Set target
	 *
	 * @param string $a_val target
	 */
	function setTarget($a_val)
	{
		$this->target = $a_val;
	}
	
	/**
	 * Get target
	 *
	 * @return string target
	 */
	function getTarget()
	{
		return $this->target;
	}

	/**
	 * Set oral
	 *
	 * @param string $a_val oral
	 */
	function setOral($a_val)
	{
		$this->oral = $a_val;
	}

	/**
	 * Get oral
	 *
	 * @return string oral
	 */
	function getOral()
	{
		return $this->oral;
	}

	/**
	 * Set date
	 *
	 * @param date $a_val date
	 */
	function setDate($a_val)
	{
		$this->date = $a_val;
	}

	/**
	 * Get date
	 *
	 * @return date date
	 */
	function getDate()
	{
		return $this->date;
	}

	/**
	 * Set course type
	 *
	 * @param integer $a_val course type
	 */
	function setCourseType($a_val)
	{
		$this->course_type = $a_val;
	}
	
	/**
	 * Get course type
	 *
	 * @return integer course type
	 */
	function getCourseType()
	{
		return $this->course_type;
	}

	/**
	 * Read
	 */
	function read()
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM cd_oral_target ".
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);
		$rec = $ilDB->fetchAssoc($set);
		$this->setUserId($rec["user_id"]);
		$this->setLanguage($rec["lang"]);
		$this->setTarget($rec["target"]);
		$this->setOral($rec["oral"]);
		$this->setCourseType($rec["course_type"]);
		$this->setDate($rec["tdate"]);
	}

	/**
	 * Save
	 */
	function save()
	{
		global $ilDB;

		$id = $ilDB->nextId("cd_oral_target");
		$ilDB->manipulate("INSERT INTO cd_oral_target ".
			"(id, user_id, lang, target, oral, course_type, tdate) VALUES (".
			$ilDB->quote($id, "integer").",".
			$ilDB->quote($this->getUserId(), "integer").",".
			$ilDB->quote($this->getLanguage(), "text").",".
			$ilDB->quote($this->getTarget(), "text").",".
			$ilDB->quote($this->getOral(), "text").",".
			$ilDB->quote($this->getCourseType(), "text").",".
			$ilDB->quote($this->getDate(), "date").
			")");
	}

	/**
	 * Update
	 *
	 * @param
	 * @return
	 */
	function update()
	{
		global $ilDB;

		$ilDB->manipulate("UPDATE cd_oral_target SET ".
			" user_id = ".$ilDB->quote($this->getUserId(), "integer").",".
			" lang = ".$ilDB->quote($this->getLanguage(), "text").",".
			" target = ".$ilDB->quote($this->getTarget(), "text").",".
			" oral = ".$ilDB->quote($this->getOral(), "text").",".
			" course_type = ".$ilDB->quote($this->getCourseType(), "integer").",".
			" tdate = ".$ilDB->quote($this->getDate(), "date").
			" WHERE id = ".$ilDB->quote($this->getId(), "integer")
			);
	}

	/**
	 * Delete
	 *
	 * @param
	 * @return
	 */
	function delete()
	{
		global $ilDB;

		$ilDB->manipulate("DELETE FROM cd_oral_target WHERE ".
			" id = ".$ilDB->quote($this->getId(), "integer")
			);

	}

	
	////
	//// Old implementation
	////



	/**
	 * Get oral/target marks of user and language
	 *
	 * @param
	 * @return
	 */
	/*
	static function read($a_user, $a_lang)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM cd_oral_target ".
			" WHERE user_id = ".$ilDB->quote($a_user, "integer").
			" AND lang = ".$ilDB->quote($a_lang, "text")
			);
		$rec  = $ilDB->fetchAssoc($set);
		return array("oral" => $rec["oral"], 
			"target" => $rec["target"],
			"course_type" => $rec["course_type"]
			);
	}*/
	
	/**
	 * Write oral and target mark for user
	 *
	 * @param
	 * @return
	 */
	/*
	static function write($a_user, $a_lang, $a_oral, $a_target, $a_course_type)
	{
		global $ilDB;
		
		$ilDB->replace("cd_oral_target", array(
			"oral" => array("text", $a_oral),
			"target" => array("text", $a_target),
			"course_type" => array("integer", $a_course_type)
			),
			array(
			"user_id" => array("integer", $a_user),
			"lang" => array("text", $a_lang))
		);
	}*/
	
	/**
	 * Read set values for user
	 *
	 * @param int $a_user_id
	 * @return array
	 */
	static function readUserValues($a_user_id, $a_only_latest = false)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT * FROM cd_oral_target ".
			" WHERE user_id = ".$ilDB->quote($a_user_id, "integer").
			" ORDER BY tdate DESC"
			);
		$v = array();
		$langs = array();
		while ($rec  = $ilDB->fetchAssoc($set))
		{
			if ($rec["oral"] != "" || $rec["target"] != "" || $rec["course_type"] > 0)
			{
				if (!$a_only_latest || !in_array($rec["lang"], $langs))
				{
					$nval  = array("oral" => $rec["oral"], "lang" => $rec["lang"],
						"target" => $rec["target"], "course_type" => $rec["course_type"],
						"date" => $rec["tdate"], "id" => $rec["id"]);
					if ($a_only_latest)
					{
						$v[$rec["lang"]] = $nval;
					}
					else
					{
						$v[] = $nval;
					}
					$langs[$rec["lang"]] = $rec["lang"];
				}
			}
		}
		return $v;
	}
}

?>
