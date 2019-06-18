<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API\Course;
use ILIAS\API as API;

/**
 * Course api factory parameters
 *
 * @author killing@leifos.de
 */
class Parameters implements API\Int\Parameters
{
	protected $course_ref_id = null;

	/**
	 * Constructor
	 */
	public function __construct(int $course_ref_id = null)
	{
		$this->course_ref_id = $course_ref_id;
	}

	/**
	 * @return int
	 */
	function getCourseRefId(): int
	{
		return $this->course_ref_id;
	}
}