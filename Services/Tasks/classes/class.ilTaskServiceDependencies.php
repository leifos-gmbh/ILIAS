<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Task service dependencies
 *
 * @author killing@leifos.de
 * @ingroup ServiceTasks
 */
class ilTaskServiceDependencies
{
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var \ILIAS\DI\UIServices
	 */
	protected $ui;

	/**
	 * @var \ilObjUser
	 */
	protected $user;

	/**
	 * @var \ilAccessHandler
	 */
	protected $access;

	/**
	 * Constructor
	 * @param ilObjUser $user
	 * @param ilLanguage $lng
	 * @param \ILIAS\DI\UIServices $ui
	 */
	public function __construct(ilObjUser $user, ilLanguage $lng, \ILIAS\DI\UIServices $ui,
								\ilAccessHandler $access)
	{
		$this->lng = $lng;
		$this->ui = $ui;
		$this->user = $user;
		$this->access = $access;
	}

	/**
	 * Get language object
	 *
	 * @return \ilLanguage
	 */
	public function language(): \ilLanguage
	{
		return $this->lng;
	}

	/**
	 * Get current user
	 *
	 * @return \ilObjUser
	 */
	public function user(): \ilObjUser
	{
		return $this->user;
	}

	/**
	 * Get ui service
	 *
	 * @return \ILIAS\DI\UIServices
	 */
	public function ui(): \ILIAS\DI\UIServices
	{
		return $this->ui;
	}

	/**
	 * Get access
	 *
	 * @return \ilAccessHandler
	 */
	protected function getAccess(): \ilAccessHandler
	{
		return $this->access;
	}

}