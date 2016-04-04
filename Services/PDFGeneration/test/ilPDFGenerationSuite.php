<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilPasswordTestSuite
 * @package ilPdfGenerator
 */
class ilPDFGenerationSuite extends PHPUnit_Framework_TestSuite
{
	/**
	 * @return self
	 */
	public static function suite()
	{
		if (defined('ILIAS_PHPUNIT_CONTEXT'))
		{
			include_once("./Services/PHPUnit/classes/class.ilUnitUtil.php");
			ilUnitUtil::performInitialisation();
		}
		else
		{
			chdir( dirname( __FILE__ ) );
			chdir('../../../');
		}
		
		// Set timezone to prevent notices
		date_default_timezone_set('Europe/Berlin');

		$suite = new self();

		require_once dirname(__FILE__) . '/ilPdfGeneratorConstantsTest.php';
		$suite->addTestSuite('ilPdfGeneratorConstantsTest');

		require_once dirname(__FILE__) . '/ilPdfGeneratorConstantsTest.php';
		$suite->addTestSuite('ilPdfGeneratorConstantsTest');

		require_once dirname(__FILE__) . '/ilPhantomJsHtmlToPdfTransformerTest.php';
		$suite->addTestSuite('ilPhantomJsHtmlToPdfTransformerTest');

		require_once dirname(__FILE__) . '/ilWebkitHtmlToPdfTransformerTest.php';
		$suite->addTestSuite('ilWebkitHtmlToPdfTransformerTest');

		return $suite;
	}
} 