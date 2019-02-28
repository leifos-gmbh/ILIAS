<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use Datto\JsonRpc\Evaluator;
use Datto\JsonRpc\Exceptions\ArgumentException;
use Datto\JsonRpc\Exceptions\MethodException;

class ilPageEditorRpcApi implements Evaluator
{
	/**
	 * @param string $method
	 * @param array $arguments
	 * @return int|mixed
	 * @throws ArgumentException
	 * @throws MethodException
	 */
	public function evaluate($method, $arguments)
	{
		global $DIC;

		//$request_content = $DIC->http()->request()->getBody()->getContents();
		//$request_content = print_r($DIC->http()->request()->getBody()->getContents(), true);
		//$request_content = "*";
		if ($method === 'add') {
			return self::add($arguments);
		}

		if ($method === 'passthru') {
			return $request_content."---";
		}

		throw new MethodException();
	}

	/**
	 * @param $arguments
	 * @return int
	 * @throws ArgumentException
	 */
	private static function add($arguments)
	{
		@list($a, $b) = $arguments;

		if (!is_int($a) || !is_int($b)) {
			throw new ArgumentException();
		}

		return $a + $b;
	}
}
