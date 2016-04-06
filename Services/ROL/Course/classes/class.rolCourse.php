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
	 * Set asix key
	 *
	 * @param string $a_val asix key
	 */
	function setAsixKey($a_val)
	{
		$this->asix_key = $a_val;
	}

	/**
	 * Get asix key
	 *
	 * @return string asix key
	 */
	function getAsixKey()
	{
		return $this->asix_key;
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
		$this->setAsixKey($rec["rol_asix_key"]);
	}


	/**
	 * Update
	 */
	function update()
	{
		$this->db->manipulate($q = "UPDATE crs_settings SET ".
				" min_onlinetime = ".$this->db->quote($this->getMinOnlinetime(), "integer").
				", rol_asix_key = ".$this->db->quote($this->getAsixKey(), "text").
				" WHERE obj_id = ".$this->db->quote($this->id, "integer")
				);
	}

	/**
	 * Clone course
	 *
	 * @param int $a_new_course_id new course id
	 */
	function cloneCourse($a_new_course_id)
	{
		$new_c = new rolCourse($a_new_course_id);
		$new_c->setMinOnlinetime($this->getMinOnlinetime());
		$new_c->setAsixKey($this->getAsixKey());
		$new_c->update();
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

	/**
	 * Get all courses with minimal online time
	 *
	 * @return array of missing notifications obj_id, ref_id, users
	 */
	static function getMissingUserNotifications()
	{
		global $ilDB;

		$log = ilLoggerFactory::getLogger('raiffrol');

		$set = $ilDB->query("SELECT DISTINCT r.ref_id, r.obj_id, e.usr_id FROM object_data o JOIN crs_settings c ON (o.obj_id = c.obj_id) ".
				" JOIN object_reference r ON (o.obj_id = r.obj_id) ".
				" JOIN read_event e ON (r.obj_id = e.obj_id) ".
				" LEFT JOIN user_course_email m ON (r.obj_id = m.course_id AND e.usr_id = m.user_id) ".
				" WHERE c.min_onlinetime > ".$ilDB->quote(0, "integer").
				" AND m.course_id IS NULL AND m.user_id IS NULL"
			);
		$missing = array();
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$missing[$rec["obj_id"]]["obj_id"] = $rec["obj_id"];
			$missing[$rec["obj_id"]]["ref_id"] = $rec["ref_id"];
			$missing[$rec["obj_id"]]["users"][] = $rec["usr_id"];
		}

		//$log->debug("");

		return $missing;
	}


}

?>