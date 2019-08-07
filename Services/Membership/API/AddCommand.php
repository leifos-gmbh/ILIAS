<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API\Membership;
use ILIAS\API\Membership\Int as I;
use ILIAS\API as API;

/**
 * Add membership command
 */
class AddCommand extends API\Int\AbstractCommand implements I\AddCommand {

	/**
	 * @var int
	 */
	protected $user_id;

	/**
	 * @var int
	 */
	protected $local_role_id;

	/**
	 * CreateCommand constructor.
	 * @param int $user_id
	 * @param int $local_role_id
	 */
	public function __construct(API\Int\FactoryCollection $factory_collection, int $user_id, int $local_role_id) {
		parent::__construct($factory_collection);
		$this->user_id = $user_id;
		$this->local_role_id = $local_role_id;
	}

	/**
	 * @return int
	 */
	public function getUserId(): int {
		return $this->user_id;
	}

	/**
	 * @return int
	 */
	public function getLocalRoleId(): int {
		return $this->local_role_id;
	}


	// possible mutators

}

