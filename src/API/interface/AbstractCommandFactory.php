<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API\Int;

/**
 * Command factory. Returns commands or other command factories
 *
 * @author killing@leifos.de
 */
abstract class AbstractCommandFactory implements CommandFactory
{
	/**
	 * @var FactoryCollection
	 */
	protected $factory_collection;

	/**
	 * @var Parameters
	 */
	protected $parameters = null;

	/**
	 * Constructor
	 */
	public function __construct(FactoryCollection $factory_collection, Parameters $parameters = null) {
		$this->factory_collection = $factory_collection;
		$this->factory_collection->add($this);
		$this->parameters = $parameters;
	}
	
	/**
	 * @inheritdoc
	 */
	public function getParameters(): ?Parameters
	{
		return $this->parameters;
	}
	
}