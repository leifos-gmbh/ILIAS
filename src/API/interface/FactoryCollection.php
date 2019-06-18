<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API\Int;

/**
 * Factory collection
 *
 * @author killing@leifos.de
 */
interface FactoryCollection extends \IteratorAggregate
{
	/**
	 * @param CommandFactory $factory
	 */
	public function add(CommandFactory $factory);
}