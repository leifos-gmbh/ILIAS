<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API\Int;

/**
 * Command factory. Returns commands or other command factories
 *
 * @author killing@leifos.de
 */
interface CommandFactory
{
	/**
	 * Get parameters
	 * @return Parameters
	 */
	public function getParameters(): ?Parameters;

}