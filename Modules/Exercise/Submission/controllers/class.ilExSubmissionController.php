<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 *
 * TODO :: This class was renamed and not used now. It will replace the class ilExSubmission and take its name.
 *
 * 
 * Exercise submission
 *
 * @author Jesús López <lopez@leifos.com>
 * @ingroup ModulesExercise
 */
class ilExSubmissionController
{
	/**
	 * @var ilDBInterface
	 */
	protected $db;

	/**
	 * @var ilExSubmissionRepositoryInterface
	 */
	protected $submission;

	public function __construct(ilExSubmissionRepositoryInterface $submission)
	{
		global $DIC;

		$this->db = $DIC->database();

		$this->submission = $submission;
	}

}