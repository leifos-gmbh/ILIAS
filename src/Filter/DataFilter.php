<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Filter;

/**
 *
 *
 * @author Alex Killing <killing@leifos.de
 * @package ILIAS\Filter
 */
class DataFilter extends AbstractDataFilter
{
	/**
	 * Input date filter
	 *
	 * @param int|bool|string|array $data untrusted input data
	 */
	function __construct($data)
	{
		$this->data = $data;
	}

}
