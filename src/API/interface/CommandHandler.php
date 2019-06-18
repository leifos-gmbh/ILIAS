<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API\Int;


/**
 * Command handler
 *
 * @author killing@leifos.de
 */
interface CommandHandler
{
	/**
	 * @param CommandFactory $command_handler
	 */
	public function getConfigForSubHandler(CommandHandler $command_handler);

	/**
	 * @param CommandHandler $command_handler
	 * @return int
	 */
	public function checkPolicyForSubHandler(CommandHandler $command_handler, int $actor_id): int;

	/**
	 * @param Command $command
	 * @return int
	 */
	public function checkPolicyForCommand(Command $command, int $actor_id): int;

	/**
	 * @param Command $command
	 */
	public function handle(Command $command, int $actor_id);

}