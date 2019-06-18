<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API;
use ILIAS\API\Int as I;

/**
 *
 *
 * @author killing@leifos.de
 */
class CommandFactory implements I\CommandFactory
{
	protected $factory_collection;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->factory_collection;
	}

	/**
	 * @inheritdoc
	 */
	public function addParentFactory(I\CommandFactory $factory) {

	}
}