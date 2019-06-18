<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API\Membership;

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
	public function checkPolicyForCommand(API\Int\Command $command, int $actor_id): int
	{
		if ($actor_id === 7)
		{
			return self::POLICY_OK;
		}

		return self::POLICY_FAILED;
	}

	/**
	 * @inheritdoc
	 */
	public function handle(API\Int\Command $command, int $actor_id)
	{
		if ($command instanceof AddCommand)
		{
			echo "Succeded to perform ".get_class($command)." for actor $actor_id";
			return;
		}

		throw new Exc\UnknownCommand("Command unknown ".get_class($command));
	}

}