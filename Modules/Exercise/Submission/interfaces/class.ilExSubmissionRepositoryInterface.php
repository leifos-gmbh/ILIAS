<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface for submission repository
 *
 * @author Jesús López <lopez@leifos.com>
 */
interface ilExSubmissionRepositoryInterface
{
	/**
	 * commented methods are not tasks of the repository and they have to be
	 * implemented in the controller or intermediate abstractions.
	 **/

	public function getSubmissionType();
	//public function getAssignment();
	//public function getTeam();
	//public function getPeerReview();
	//public function validatePeerReviews();
	public function getUserId();
	public function getLastSubmission(int $assignment_id, string $sql_and): string;
	//public function getUserIds(); // has to be refactored, remove the logic from the repo
	//public function getFeedbackId() //??
	//public function getSubmissionFiles() // refactor and renamed to getSubmissionData

}