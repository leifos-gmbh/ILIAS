<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API\Membership;
use ILIAS\API\Membership\Int as I;
use ILIAS\API as API;

/**
 *
 *
 * @author killing@leifos.de
 */
class CommandFactory extends API\Int\AbstractCommandFactory implements I\CommandFactory
{
	/**
	 * Constructor
	 */
	public function __construct(API\Int\FactoryCollection $factory_collection, int $course_ref_id = null)
	{
		parent::__construct($factory_collection);
		$this->course_ref_id = $course_ref_id;
	}

	/**
	 * @inheritdoc
	 */
	public function add(int $user_id, int $local_role_id): I\AddCommand {
		return new AddCommand($this->factory_collection, $user_id, $local_role_id);
	}


}