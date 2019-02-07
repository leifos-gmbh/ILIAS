<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Task service
 *
 * @author killing@leifos.de
 * @ingroup ServiceTasks
 */
class ilDerivedTaskService
{
	/**
	 * @var ilTaskServiceDependencies
	 */
	protected $_deps;

	/**
	 * @var ilTaskService
	 */
	protected $service;

	/**
	 * Constructor
	 * @param ilTaskService $service
	 * @param ilTaskServiceDependencies $_deps
	 */
	public function __construct(ilTaskService $service, ilTaskServiceDependencies $_deps)
	{
		$this->_deps = $_deps;
		$this->service = $service;
	}

	/**
	 * Subservice for derived tasks
	 *
	 * @return ilDerivedTaskService
	 */
	public function factory(): ilDerivedTaskFactory
	{
		return new ilDerivedTaskFactory($this->service, $this->_deps);
	}


}