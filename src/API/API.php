<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API;

/**
 *
 *
 * @author killing@leifos.de
 */
class API
{
	/**
	 * @var FactoryCollection
	 */
	protected $factory_collection;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// should be injected...
		$this->factory_collection = new FactoryCollection();
		$this->command_bus = new CommandBus();
	}

	/**
	 * Get initial (empty) factory collection for an api call
	 */
	private function getInitialFactoryCollection()
	{
		return clone $this->factory_collection;
	}

	/**
	 * @inheritdoc
	 */
	public function course(int $ref_id = null): Course\Int\CommandFactory {
		return new \ILIAS\API\Course\CommandFactory($this->getInitialFactoryCollection(), $ref_id);
	}


	/**
	 * @param Int\Command $command
	 * @param int $actor_id
	 * @return CommandResponse|null
	 */
	public function dispatch(Int\Command $command, int $actor_id): ?CommandResponse {
		$this->command_bus->dispatch($command, $actor_id);

		return null;
	}

}