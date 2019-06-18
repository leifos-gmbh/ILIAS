<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\API\Exceptions;

/**
 * Unknown handler exception
 *
 * @author killing@leifos.de
 */
class UnknownHandler extends \Exception
{
	/**
	 * Constructor
	 */
	public function __construct($m)
	{
		parent::__construct($m);
	}

}