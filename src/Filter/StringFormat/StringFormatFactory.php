<?php

namespace ILIAS\Filter;

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * String format factory
 *
 * @author Alex Killing <killing@leifos.de
 * @package ILIAS\Filter
 */
class StringFormatFactory
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
	 * Html format
	 *
	 * @param \ilHtmlPurifierAbstractLibWrapper $purifier
	 * @return HtmlStringFormat
	 */
	public function html(\ilHtmlPurifierAbstractLibWrapper $purifier): HtmlStringFormat
	{
		return new HtmlStringFormat($purifier);
	}

	/**
	 * Xml format
	 *
	 * @param \ilHtmlPurifierAbstractLibWrapper $purifier
	 * @return XmlStringFormat
	 */
	public function xml(): XmlStringFormat
	{
		return new XmlStringFormat();
	}

}
