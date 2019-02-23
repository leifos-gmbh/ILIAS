<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("Modules/Exercise/Submission/interfaces/class.ilExSubmissionRepositoryInterface.php");


/**
 * Interface for submission repository
 *
 * @author Jesús López <lopez@leifos.com>
 */
class ilExSubmissionRepository implements ilExSubmissionRepositoryInterface
{
	protected $db;

	public function __construct(ilDBInterface $db)
	{
		$this->db = $db;
	}



	public function getSubmissionType()
	{
		return 3;
	}

	public function getUserId()
	{
		return 4;
	}

	/**
	 * @param int $assignment_id
	 * @param string $sql_and
	 * @return string
	 */
	public function getLastSubmission(int $assignment_id, string $sql_and): string
	{

		$this->db->setLimit(1);

		$q = "SELECT obj_id,user_id,ts FROM exc_returned".
			" WHERE ass_id = ".$this->db->quote($assignment_id, "integer").
			" AND ".$sql_and.
			" AND (filename IS NOT NULL OR atext IS NOT NULL)".
			" AND ts IS NOT NULL".
			" ORDER BY ts DESC";
		$usr_set = $this->db->query($q);
		$array = $this->db->fetchAssoc($usr_set);
		return ilUtil::getMySQLTimestamp($array["ts"]);
	}
}