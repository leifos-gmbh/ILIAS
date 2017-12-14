<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once 'Services/User/Gallery/classes/class.ilAbstractUsersGalleryCollectionProvider.php';

/**
 * Class ilUsersGalleryParticipants
 */
class ilUsersGalleryParticipants extends ilAbstractUsersGalleryCollectionProvider
{
	/**
	 * @var ilParticipants
	 */
	protected $participants;

	/**
	 * @var array
	 */
	protected $users = array();

	/**
	 * @param ilParticipants $participants
	 */
	public function __construct(ilUsersGalleryParticipants $participants)
	{
		$this->participants = $participants;
	}

	/**
	 * @param int[] $usr_ids
	 * @return ilObjUser[]
	 */
	protected function getUsers(array $usr_ids)
	{
		$users = [];

		foreach($usr_ids as $usr_id)
		{
			if(isset($this->users[$usr_id]))
			{
				continue;
			}

			/**
			 * @var $user ilObjUser
			 */
			if(!($user = ilObjectFactory::getInstanceByObjId($usr_id, false)))
			{
				continue;
			}

			if(!$user->getActive())
			{
				continue;
			}

			$users[$user->getId()] = $user;
			$this->users[$user->getId()] = true;
		}

		return $users;
	}

	/**
	 * @inheritdoc
	 */
	public function getGroupedCollections()
	{
		/**
		 * @var $DIC ILIAS\DI\Container
		 */
		global $DIC, $ilUser;

		$groups = [];
		
		$rbac_perm = 'manage_members';
		$participants = $this->participants->getParticipants();
		if(in_array($ilUser->getId(), $participants))
		{
			$rbac_perm = 'read';
		}
		
		$contacts = $DIC->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
			$rbac_perm,
			'manage_members',
			(int) $_GET['ref_id'],
			$this->participants->getContacts()
		);
		$admins = $DIC->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
			$rbac_perm,
			'manage_members',
			(int) $_GET['ref_id'],
			$this->participants->getAdmins()
		);
		$tutors = $DIC->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
			$rbac_perm,
			'manage_members',
			(int) $_GET['ref_id'],
			$this->participants->getTutors()
		);
		$members = $DIC->access()->filterUserIdsByRbacOrPositionOfCurrentUser(
			$rbac_perm,
			'manage_members',
			(int) $_GET['ref_id'],
			$this->participants->getMembers()
		);
			

		foreach([
			array($contacts, true, $DIC->language()->txt('crs_mem_contact')),
			array($admins  , false, ''),
			array($tutors  , false, ''),
			array($members , false, '')
		] as $users)
		{
			$group = $this->getPopulatedGroup($this->getUsers($users[0]));
			$group->setHighlighted($users[1]);
			$group->setLabel($users[2]);
			$groups[] = $group;
		}

		return $groups;
	}
} 