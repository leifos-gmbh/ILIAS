<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API\Exceptions;

/**
 * Policy failed exception
 *
 * @author killing@leifos.de
 */
class PolicyFailed extends \Exception
{
	/**
	 * Constructor
	 */
	public function __construct($m)
	{
		parent::__construct($m);
	}

}