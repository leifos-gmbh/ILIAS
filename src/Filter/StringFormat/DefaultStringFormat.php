<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Filter;

/**
 * Default string format, allows HTML tags "b", "i", "strong", "em", "code", "cite", "gap", "sub", "sup", "pre", "strike", "bdo"
 *
 * @author Alex Killing <killing@leifos.de
 * @package ILIAS\Filter
 */
class DefaultStringFormat implements StringFormatInt
{
	public function __construct() {
	}

	/**
	 * @inheritdoc
	 */
	function is(string $str): bool
	{
		if ($this->cast($str) == $str) {
			return true;
		}
		return false;
	}

	/**
	 * @inheritdoc
	 */
	function cast(string $str): string
	{
		return \ilUtil::stripSlashes($str);
	}

}
?>