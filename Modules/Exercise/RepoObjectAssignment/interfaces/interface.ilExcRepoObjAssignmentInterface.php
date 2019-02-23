<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface for assignment types
 *
 * @author Alex Killing <killing@leifos.de>
 */
interface ilExcRepoObjAssignmentInterface
{
	// TODO :: Why the assignment info has to deal with users?

	/**
	 * Get assignment(s) info of repository object
	 *
	 * @param int $a_ref_id ref id
	 * @param int $a_user_id user id
	 * @return ilExcRepoObjAssignmentInfoInterface[]
	 */
	function getAssignmentInfoOfObj($a_ref_id, $a_user_id);

	// TODO :: Why the assignment info has to deal with users? If it is permission related why not check it on Exercise level?
	/**
	 * Get assignment access info for a repository object
	 *
	 * @param int $a_ref_id
	 * @param int $a_user_id
	 * @return ilExcRepoObjAssignmentAccessInfoInterface
	 */
	function isGranted($a_ref_id, $a_user_id);

}