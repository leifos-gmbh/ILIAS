<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Derived task providers factory
 *
 * @author killing@leifos.de
 * @ingroup ServicesTasks
 */
class ilDerivedTaskProviderMasterFactory
{
	/**
	 * @var ilTaskService
	 */
	protected $service;

	/**
	 * @var ilTaskServiceDependencies
	 */
	protected $_deps;

	/**
	 * @var array
	 */
	protected static $provider_factories = array(
		ilExerciseDerivedTaskProviderFactory::class
	);

	/**
	 * Constructor
	 */
	public function __construct(ilTaskService $service, ilTaskServiceDependencies $deps)
	{
		$this->service = $service;
		$this->_deps = $deps;
	}

	/**
	 * Get all task providers
	 *
	 * @param bool $active_only get only active providers
	 * @param int $user_id get instances for user with user id
	 * @return ilLearningHistoryProviderInterface[]
	 */
	public function getAllProviders($active_only = false, $user_id = null)
	{
		$providers = array();

		if ($user_id == 0) {
			$user_id = $this->_deps->user()->getId();
		}

		foreach (self::$provider_factories as $provider_factory_class) {
			$provider_factory = new $provider_factory_class($this->service);
			foreach ($provider_factory->getProviders() as $provider)
			{
				if (!$active_only || $provider->isActive())
				{
					$providers[] = $provider;
				}
			}
		}

		return $providers;
	}
}
