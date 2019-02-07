<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Task service
 *
 * @author killing@leifos.de
 * @ingroup ServiceTasks
 */
class ilTaskService
{
	/**
	 * @var ilTaskServiceDependencies
	 */
	protected $_deps;

	/**
	 * Constructor
	 * @param ilObjUser $user
	 * @param ilLanguage $lng
	 * @param \ILIAS\DI\UIServices $ui
	 * @param ilAccessHandler $access
	 */
	public function __construct(ilObjUser $user, ilLanguage $lng, \ILIAS\DI\UIServices $ui,
								\ilAccessHandler $access)
	{
		$this->_deps = new ilTaskServiceDependencies($user, $lng, $ui, $access);
	}


	/**
	 * Subservice for derived tasks
	 *
	 * @return ilDerivedTaskService
	 */
	public function derived()
	{
		return new ilDerivedTaskService($this, $this->_deps);
	}


}