<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API\Course;

use ILIAS\API as API;
use \ILIAS\API\Exceptions as Exc;


/**
 * Command handler
 *
 * @author killing@leifos.de
 */
class CommandHandler extends API\Int\AbstractCommandHandler
{
	/**
	 * @inheritdoc
	 */
	public function getConfigForSubHandler(API\Int\CommandHandler $sub_handler)
	{
		// @todo check sub handlers and configure them

		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function checkPolicyForSubHandler(API\Int\CommandHandler $sub_handler, int $actor_id): int
	{
		// membership subhandler
		if ($sub_handler instanceof \ILIAS\API\Membership\CommandHandler)
		{
			// check manage members permission

			/** @var Parameters $p */
			$p = $this->getParameters();

			// pseudo check: if ref id is 7 everything is ok
			if ($p->getCourseRefId() == 7)
			{
				return self::POLICY_OK;
			};
		}

		return self::POLICY_FAILED;
	}

	/**
	 * @inheritdoc
	 */
	public function checkPolicyForCommand(API\Int\Command $command, int $actor_id): int
	{
		// create command
		if ($command instanceof CreateCommand)
		{
			/** @var CreateCommand $command */
			$parent_ref_id = $command->getParentRefId();

			// pseudo check: if ref id is 7 everything is ok
			if ($parent_ref_id == 7)
			{
				return self::POLICY_OK;
			};
		}
		return self::POLICY_FAILED;
	}

	/**
	 * @inheritdoc
	 */
	public function handle(API\Int\Command $command, int $actor_id)
	{
		// create command
		if ($command instanceof CreateCommand)
		{
			/** @var CreateCommand $command */
			$title = $command->getTitel();
			$description = $command->getDescription();
			$parent_ref_id = $command->getParentRefId();

			// now code for performing the command
			// ....

			return;
		}
		throw new Exc\UnknownCommand("Command unknown $command");
	}

}