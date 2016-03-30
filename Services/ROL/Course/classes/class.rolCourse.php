<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * GUI patch class for course
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class rolCourse
{
	/**
	 * @var int
	 */
	protected $id;

	/**
	 * Constructor
	 *
	 * @param int $a_id Course ID
	 */
	function __construct($a_id)
	{
		global $ilDB;

		$this->id = $a_id;
		$this->db = $ilDB;
		if ($this->id > 0)
		{
			$this->read();
		}
	}

	/**
	 * Set min online time
	 *
	 * @param int $a_val min online time	
	 */
	function setMinOnlinetime($a_val)
	{
		$this->min_onlinetime = $a_val;
	}
	
	/**
	 * Get min online time
	 *
	 * @return int min online time
	 */
	function getMinOnlinetime()
	{
		return $this->min_onlinetime;
	}

	/**
	 * Read
	 */
	function read()
	{
		$set = $this->db->query("SELECT * FROM crs_settings ".
			" WHERE obj_id  = ".$this->db->quote($this->id, "integer")
		);
		$rec = $this->db->fetchAssoc($set);
		$this->setMinOnlinetime($rec["min_onlinetime"]);
	}


	/**
	 * Update
	 */
	function update()
	{
		$this->db->manipulate("UPDATE crs_settings SET ".
			" min_onlinetime = ".$this->db->quote($this->getMinOnlinetime(), "integer").
			" WHERE obj_id = ".$this->db->quote($this->id, "integer")
			);
	}

	/**
	 * Get time spent in object
	 *
	 * @param int $a_user_id user id
	 * @param int $a_ref_id object ref id
	 * @return int
	 */
	public static function lookupOnlinetimeForObjectAndUser($a_user_id, $a_ref_id)
	{
		include_once("./Services/Tracking/classes/class.ilTrQuery.php");

		$obj_id = ilObject::_lookupObjId($a_ref_id);

		$data = ilTrQuery::getObjectsStatusForUser($a_user_id, array($obj_id => $a_ref_id));

		/*$data = ilTrQuery::getDataForObjectAndUser(
			$a_ref_id,
			array($a_user_id),
			'',
			'',
			'',
			'',
			array(),
			array('spent_seconds' => 'spent_seconds')
		);*/
		return (int) $data[0]['spent_seconds'];
	}


}

?>