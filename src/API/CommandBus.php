<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API;

use ILIAS\API\Int\AbstractCommandHandler;
use ILIAS\API\Exceptions as Exc;
use ILIAS\API\Int\CommandHandler;

/**
 *
 *
 * @author killing@leifos.de
 */
class CommandBus
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		// should be injected
		$this->handler_factory = new \ILIAS\API\HandlerFactory();
	}

	/**
	 * Dispatch command, checks policy on all handlers
	 * that correspond to the involved factories.
	 *
	 * Parent handlers can configure subsequent command handlers.
	 *
	 * The final command handler checks the policy on the command and handles it.
	 *
	 * @param Int\Command $command
	 * @param int $actor_id
	 * @throws Exc\PolicyFailed
	 */
	public function dispatch(Int\Command $command, int $actor_id)
	{
		/** @var CommandHandler $parent_handler */
		$parent_handler = null;
		/** @var CommandFactory $f */
		foreach ($command->getFactoryCollection() as $f)
		{
			// via handler resolver
			/** @var CommandHandler $handler */
			$handler = $this->handler_factory->getHandlerForCommandFactory($f);

			// allow parent to config sub handler
			if (is_object($parent_handler)) {
				$config = $parent_handler->getConfigForSubHandler($handler);
				if ($config !== null) {
//					$handler->config($config);
				}

				// policy check on parent handler
				$result = $parent_handler->checkPolicyForSubHandler($handler, $actor_id);
				if ($result !== AbstractCommandHandler::POLICY_OK)
				{
					throw new Exc\PolicyFailed("Policy check failed in ".get_class($parent_handler).
						" for ".get_class($handler)." and actor ".$actor_id);
				}
			}
			$parent_handler = $handler;
		}

		// policy check on handler
		$result = $handler->checkPolicyForCommand($command, $actor_id);
		if ($result !== AbstractCommandHandler::POLICY_OK)
		{
			throw new Exc\PolicyFailed("Policy check failed in ".get_class($handler).
				" for ".get_class($command)." and actor ".$actor_id);
		}

		$handler->handle($command, $actor_id);
	}


}