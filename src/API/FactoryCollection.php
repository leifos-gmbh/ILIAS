<?php

declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API;
use ILIAS\API\Int as I;

/**
 * Collection of factories
 *
 * @author killing@leifos.de
 */
class FactoryCollection implements I\FactoryCollection
{
	private $collection = [];

	/**
	 * FactoryCollection constructor.
	 * @param I\CommandFactory[] $collection
	 */
	public function __construct($collection = [])
	{
		$this->collection = $collection;
	}
	
	/**
	 * @inheritdoc
	 */
	public function add(I\CommandFactory $factory)
	{
		$this->collection[] = $factory;
	}
	

	/**
	 * @return \ArrayIterator
	 */
	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->collection);
	}
}