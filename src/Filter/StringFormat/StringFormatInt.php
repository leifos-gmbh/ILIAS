<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Filter;

/**
 * String format interface
 *
 * @author Alex Killing <killing@leifos.de
 * @package ILIAS\Filter
 */
interface StringFormatInt
{
	/**
	 * Is string in defined format?
	 *
	 * @param string $str
	 * @return bool
	 */
	function is(string $str): bool;

	/**
	 * Cast/sanitize string into defined format
	 *
	 * @param string $str
	 * @return string
	 */
	function cast(string $str): string;

}
?>