<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API\Int;

/**
 *
 *
 * @author killing@leifos.de
 */
abstract class AbstractCommand implements Command
{
	/**
	 * @var FactoryCollection
	 */
	protected $factory_collection;

	/**
	 * Constructor
	 */
	public function __construct(FactoryCollection $factory_collection) {
		$this->factory_collection = $factory_collection;
	}

	/**
	 * @return FactoryCollection
	 */
	public function getFactoryCollection(): FactoryCollection
	{
		return $this->factory_collection;
	}

}