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
		// could be injected...
		$this->factory_collection = new FactoryCollection();
	}

	/**
	 * @inheritdoc
	 */
	function course(int $ref_id = null): \ILIAS\API\Course\Int\CommandFactory {
		return new \ILIAS\API\Course\CommandFactory($this->factory_collection, $ref_id);
	}
}