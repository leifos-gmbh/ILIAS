<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API\Int;

use \ILIAS\API\Exceptions as Exc;


/**
 * Command handler
 *
 * @author killing@leifos.de
 */
abstract class AbstractCommandHandler implements CommandHandler
{
	const POLICY_FAILED = 0;
	const POLICY_OK = 1;

	protected $parameters;

	/**
	 */
	public function __construct(Parameters $parameters = null)
	{
		$this->parameters = $parameters;
	}

	/**
	 * Get parameters
	 * @return Parameters
	 */
	public function getParameters(): Parameters
	{
		return $this->parameters;
	}

	/**
	 * @inheritdoc
	 */
	public function getConfigForSubHandler(CommandHandler $sub_handler)
	{
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function checkPolicyForSubHandler(CommandHandler $sub_handler, int $actor_id): int
	{
		return self::POLICY_FAILED;
	}

	/**
	 * @inheritdoc
	 */
	public function checkPolicyForCommand(Command $command, int $actor_id): int
	{
		return self::POLICY_FAILED;
	}

	/**
	 * @inheritdoc
	 */
	public function handle(Command $command, int $actor_id)
	{
		throw new Exc\UnknownCommand("Command unknown $command");
	}

}