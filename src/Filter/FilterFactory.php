<?php

namespace ILIAS\Filter;

use Psr\Http\Message\ServerRequestInterface;

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Input filter factory
 *
 * @author Alex Killing <killing@leifos.de
 * @package ILIAS\Filter
 */
class FilterFactory
{
	/**
	 * Input filter factory
	 *
	 * @param
	 */
	function __construct()
	{
		global $DIC;

		$this->dic = $DIC;
	}

	/**
	 * Data filters are taking any int|bool|string|array combination as input
	 *
	 * @param int|bool|string|array $data untrusted input data
	 * @return DataFilter
	 */
	public function data($data): DataFilter
	{
		return new DataFilter($data);
	}

	/**
	 * Get filter initialized with json string
	 *
	 * @param ServerRequestInterface $request
	 * @return DataFilter
	 */
	public function json(string $json): DataFilter
	{
		return new DataFilter(json_decode($json, true));
	}

	/**
	 * Get filter initialized with "GET" values from query
	 *
	 * @param ServerRequestInterface $request
	 * @return DataFilter
	 */
	public function query(ServerRequestInterface $request = null): DataFilter
	{
		if ($request === null)
		{
			$request = $this->dic->http()->request();
		}

		return new DataFilter($request->getQueryParams());
	}

	/**
	 * Get filter initialized with "POST" values from query
	 *
	 * @param ServerRequestInterface $request
	 * @return DataFilter
	 */
	public function post(ServerRequestInterface $request = null): DataFilter
	{
		if ($request === null)
		{
			$request = $this->dic->http()->request();
		}

		return new DataFilter($request->getParsedBody());
	}

	/**
	 * Get filter initialized with "COOKIE" values from query
	 *
	 * @param ServerRequestInterface $request
	 * @return DataFilter
	 */
	public function cookie(ServerRequestInterface $request = null): DataFilter
	{
		if ($request === null)
		{
			$request = $this->dic->http()->request();
		}

		return new DataFilter($request->getCookieParams());
	}

	/**
	 * String format factory
	 *
	 * @return StringFormatFactory
	 */
	public function stringFormat()
	{
		return new StringFormatFactory();
	}

}
