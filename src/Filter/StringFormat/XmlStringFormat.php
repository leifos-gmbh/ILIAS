<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\Filter;

/**
 * XML string. Must be validated agains xsd / dtd (not implemented yet)
 *
 * @author Alex Killing <killing@leifos.de
 * @package ILIAS\Filter
 */
class XmlStringFormat implements StringFormatInt
{
	protected $xsd;

	public function __construct() {
	}

	/**
	 * With xsd
	 * @param string $xsd
	 * @return XmlStringFormat
	 */
	protected function withXsd(string $xsd): XmlStringFormat
	{
		$format = clone $this;
		$format->xsd = $xsd;
		return $format;
	}

	/**
	 * @inheritdoc
	 */
	function is(string $str): bool
	{
		$xml = new \DOMDocument();
		$xml->loadXML($str);

		if ($xml->schemaValidate($this->xsd)) {
			return true;
		}

		return false;
	}

	/**
	 * @inheritdoc
	 */
	function cast(string $str): string
	{
		if (!$this->is($str))
		{
			throw new \InvalidArgumentException("Input does not validate agains XSD.");
		}
		return $str;
	}

}