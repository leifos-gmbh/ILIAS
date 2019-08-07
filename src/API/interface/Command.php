<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API\Int;

/**
 * Command
 *
 * @author killing@leifos.de
 */
interface Command
{
	/**
	 * @return FactoryCollection
	 */
	public function getFactoryCollection(): FactoryCollection;
}