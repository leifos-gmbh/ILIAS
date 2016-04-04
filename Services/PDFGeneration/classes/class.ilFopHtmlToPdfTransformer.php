<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/PDFGeneration/classes/class.ilAbstractHtmlToPdfTransformer.php';

/**
 * Class ilFopHtmlToPdfTransformer
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilFopHtmlToPdfTransformer extends ilAbstractHtmlToPdfTransformer
{
	
	protected $xsl;

	/**
	 * @param string $a_source
	 * @param string $a_target
	 */
	public function createPDFFileFromHTMLFile($a_source, $a_target)
	{
		$input = file_get_contents($a_source);
		self::createPDFFileFromFO(self::processHTML2FO($input), $a_target);
	}

	/**
	 * @param string $a_string
	 * @param string $a_target
	 */
	public function createPDFFileFromHTMLString($a_string, $a_target)
	{
		self::createPDFFileFromFO(self::processHTML2FO($a_string), $a_target);
	}

	/**
	 * Convert a print output to XSL-FO
	 *
	 * @param string $print_output The print output
	 * @return string XSL-FO code
	 * @access public
	 */
	protected function processHTML2FO($print_output)
	{
		$this->xsl = 'Services/Certificate/xml/xhtml2fo.xsl'; //ToDo: add own or other xsl

		if (!@file_exists($this->xsl)) return "";
		if (extension_loaded("tidy"))
		{
			$config = array(
				"indent"         => false,
				"output-xml"     => true,
				"numeric-entities" => true
			);
			$tidy = new tidy();
			$tidy->parseString($print_output, $config, 'utf8');
			$tidy->cleanRepair();
			$print_output = tidy_get_output($tidy);
			$print_output = preg_replace("/^.*?(<html)/", "\\1", $print_output);
		}
		$xsl = file_get_contents($this->xsl);
		$args = array( '/_xml' => $print_output, '/_xsl' => $xsl );
		$xh = xslt_create();
		$params = array();
		$output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", NULL, $args, $params);
		xslt_error($xh);
		xslt_free($xh);
		return $output;
	}

	/**
	 * Delivers a PDF file from a XSL-FO string
	 * @param string $fo The XSL-FO string
	 * @access public
	 * @return bool
	 */
	protected function deliverPDFFromFO($fo)
	{
		global $ilLog;

		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$fo_file = ilUtil::ilTempnam() . ".fo";
		$fp = fopen($fo_file, "w"); fwrite($fp, $fo); fclose($fp);
		include_once './Services/WebServices/RPC/classes/class.ilRpcClientFactory.php';
		try
		{
			$pdf_base64 = ilRpcClientFactory::factory('RPCTransformationHandler')->ilFO2PDF($fo);
			ilUtil::deliverData($pdf_base64->scalar, $this->filename, "application/pdf", false, true);
			return true;
		}
		catch(XML_RPC2_FaultException $e)
		{
			$ilLog->write(__METHOD__.': '.$e->getMessage());
			return false;
		}
		catch(Exception $e)
		{
			$ilLog->write(__METHOD__.': '.$e->getMessage());
			return false;
		}
	}

	/**
	 * @param $fo
	 * @param $target
	 * @return bool
	 */
	protected function createPDFFileFromFO($fo, $target)
	{
		global $ilLog;

		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$fo_file = ilUtil::ilTempnam() . ".fo";
		$fp = fopen($fo_file, "w"); fwrite($fp, $fo); fclose($fp);
		include_once './Services/WebServices/RPC/classes/class.ilRpcClientFactory.php';
		try
		{
			$pdf_base64 = ilRpcClientFactory::factory('RPCTransformationHandler')->ilFO2PDF($fo);
			//$pdf_binary = base64_decode($pdf_base64);
			//file_put_contents($target, $pdf_binary);
			file_put_contents($target, $pdf_base64->scalar);
			return true;
		}
		catch(XML_RPC2_FaultException $e)
		{
			$ilLog->write(__METHOD__.': '.$e->getMessage());
			return false;
		}
		catch(Exception $e)
		{
			$ilLog->write(__METHOD__.': '.$e->getMessage());
			return false;
		}
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return __CLASS__;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return 'fop';
	}

	/**
	 * @return string
	 */
	public function isActive()
	{
		$fop_set = new ilSetting('pdf_transformer_fop');
		return $fop_set->get('is_active');
	}

	public function getPathToTestHTML()
	{
		return 'Services/PDFGeneration/templates/default/test_simple.html';
	}

	public static function supportMultiSourcesFiles()
	{
		return false;
	}

	/**
	 * @return bool
	 */
	public function hasInfoInterface()
	{
		return false;
	}
}