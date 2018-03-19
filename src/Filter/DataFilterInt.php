<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Filter;

/**
 * Data filter interface. A filter holds untrusted data
 * and provides access to the data through methods that
 * ensure a trusted format.
 *
 * @author Alex Killing <killing@leifos.de
 * @package ILIAS\Filter
 */
interface DataFilterInt extends \ArrayAccess, \Iterator
{
	/**
	 * Returns data cast into an int value
	 *
	 * @return int
	 */
	function int(): int;

	/**
	 * Checks if data is an int value
	 *
	 * @return bool
	 */
	function isInt(): bool;

	/**
	 * Returns data cast into a bool value
	 *
	 * @return bool
	 */
	function bool(): bool;

	/**
	 * Checks if data is a bool value
	 *
	 * @return bool
	 */
	function isBool(): bool;

	/**
	 * Returns data cast into a valid trusted string format
	 *
	 * @param StringFormatInt|null $string_format
	 * @return string
	 * @throws \InvalidArgumentException
	 */
	function string(StringFormatInt $string_format = null): string;

	/**
	 * Checks if data is in a valid trusted string format
	 *
	 * @return bool
	 */
	function isString(StringFormatInt $string_format = null): bool;

}
?>