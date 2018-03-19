<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Filter;

/**
 * HTML string. Must use purifier to sanitize string.
 *
 * @author Alex Killing <killing@leifos.de
 * @package ILIAS\Filter
 */
class HtmlStringFormat implements StringFormatInt
{
	protected $purifier;

	public function __construct(\ilHtmlPurifierAbstractLibWrapper $purifier) {
		$this->purifier = $purifier;
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
		return $this->purifier->purify($str);
	}

}
?>

