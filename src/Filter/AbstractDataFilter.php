<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Filter;

/**
 * Class AbstractDataFilter
 * @author Alex Killing <killing@leifos.de>
 * @package ILIAS\Filter
 */
abstract class AbstractDataFilter implements DataFilterInt
{
	protected $data;

	/**
	 * @inheritdoc
	 */
	function int(): int
	{
		return (int) $this->data;
	}

	/**
	 * @inheritdoc
	 */
	function isInt(): bool
	{
		return is_int($this->data);
	}

	/**
	 * @inheritdoc
	 */
	function bool(): bool
	{
		return (bool) $this->data;
	}

	/**
	 * @inheritdoc
	 */
	function isBool(): bool
	{
		return is_bool($this->data);
	}


	/**
	 * @inheritdoc
	 */
	function string(StringFormatInt $string_format = null): string
	{
		if ($string_format === null)
		{
			$string_format = new DefaultStringFormat();
		}
		return (string) $string_format->cast($this->data);
	}

	/**
	 * @inheritdoc
	 */
	function isString(StringFormatInt $string_format = null): bool
	{
		if ($string_format === null)
		{
			$string_format = new DefaultStringFormat();
		}
		return (bool) $string_format->is($this->data);
	}

	//
	// ArrayAccess interface
	//

	/**
	 * @param mixed $offset
	 * @return bool
	 */
	public function offsetExists($offset ): bool
	{
		if (!is_array($this->data)) {
			throw new \InvalidArgumentException("Input is not an array.");
		}
		if (!isset($this->data[$offset])) {
			return false;
		}
		return true;
	}

	/**
	 * @param mixed $offset
	 * @return DataFilter
	 */
	public function offsetGet($offset): DataFilter
	{
		if (!is_array($this->data)) {
			throw new \InvalidArgumentException("Input is not an array.");
		}
		if (!isset($this->data[$offset])) {
			throw new \OutOfRangeException("Illegal offset '$offset'.");
		}
		return new DataFilter($this->data[$offset]);
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet ($offset, $value)
	{
		throw new \BadMethodCallException("DataFilter is not writable.");
	}

	/**
	 * @param mixed $offset
	 */
	public function offsetUnset($offset)
	{
		throw new \BadMethodCallException("DataFilter is not writable.");
	}

	//
	// Iterator Interface
	//

	/**
	 *
	 */
	function rewind(): void
	{
		if (!is_array($this->data)) {
			throw new \BadMethodCallException("Input is not an array.");
		}
		reset($this->data);
	}

	/**
	 * @return mixed
	 */
	function current(): mixed
	{
		if (!is_array($this->data)) {
			throw new \BadMethodCallException("Input is not an array.");
		}
		return current($this->data);
	}

	/**
	 * @return mixed
	 */
	function key(): mixed
	{
		if (!is_array($this->data)) {
			throw new \BadMethodCallException("Input is not an array.");
		}
		return key($this->data);
	}

	/**
	 *
	 */
	function next(): void
	{
		if (!is_array($this->data)) {
			throw new \BadMethodCallException("Input is not an array.");
		}
		next($this->data);
	}

	/**
	 * @return bool
	 */
	function valid(): bool
	{
		if (!is_array($this->data)) {
			throw new \BadMethodCallException("Input is not an array.");
		}
		return key($this->data) !== null;
	}

}