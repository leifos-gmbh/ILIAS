<?php
/* Copyright (c) 1998-20017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilUserAccessHandler
 *
 * Checks access for ILIAS objects
 *
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesAccessControl
 */
class ilUserAccessHandler
{
	/**
	 * @param $a_finished_id  // user id
	 * @return bool
	 */
	function checkAccess($a_finished_id)
	{
		global $ilUser;

		//Dummy code: example comparing if the current user have any org. unit in common with this user id.

		require_once("./Modules/OrgUnit/classes/class.ilObjOrgUnit.php");

		$login_user_org_ids = ilObjOrgUnitTree::_getInstance()->getOrgUnitOfUser($ilUser->getId());

		$target_user_org_ids = ilObjOrgUnitTree::_getInstance()->getOrgUnitOfUser($a_finished_id);

		$org_incommon  = array_intersect($login_user_org_ids,$target_user_org_ids);

		if(count($org_incommon) > 0) {
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Get array of user ids and return it filtered.
	 * @param array $a_finished_ids
	 * @return array
	 */
	function filterUsers(array $a_finished_ids)
	{
		$arrayUserAccess = array();

		foreach($a_finished_ids as $finished_id)
		{
			if($this->checkAccess($finished_id))
			{
				array_push($arrayUserAccess, $finished_id);
			}
		}
		return $arrayUserAccess;
	}
}