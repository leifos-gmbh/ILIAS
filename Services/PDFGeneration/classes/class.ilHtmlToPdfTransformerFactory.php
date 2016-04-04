<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once 'Services/PDFGeneration/classes/class.ilAbstractHtmlToPdfTransformer.php';
/**
 * Class ilHtmlToPdfTransformerFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilHtmlToPdfTransformerFactory
{

	const PDF_OUTPUT_DOWNLOAD	= 'D';
	const PDF_OUTPUT_INLINE		= 'I';
	const PDF_OUTPUT_FILE		= 'F';

	/**
	 * @var ilSetting
	 */
	protected $pdf_transformer_settings;

	/**
	 * @var ilLanguage $lng
	 */
	protected $lng;

	/**
	 * @var ilHtmlToPdfTransformer $valid_engines
	 */
	public static $valid_engines;

	/**
	 * @var ilHtmlToPdfTransformer transformer
	 */
	protected $transformer;

	/**
	 * @return array
	 */
	public function getValidEngines()
	{
		if(null !== self::$valid_engines)
		{
			return self::$valid_engines;
		}

		$engines = array();
		$iter = new DirectoryIterator(dirname(__FILE__));
		foreach($iter as $file)
		{
			/**
			 * @var $file SplFileInfo
			 */
			if($file->isDir())
			{
				continue;
			}

			require_once $file->getFilename();
			$class      = str_replace(array('class.', '.php'), '', $file->getBasename());
			$reflection = new ReflectionClass($class);
			if(
				!$reflection->isAbstract() &&
				$reflection->isSubclassOf('ilAbstractHtmlToPdfTransformer')
			)
			{
				$engines[$class] = new $class();
			}
		}

		return (self::$valid_engines = $engines);
	}
	/**
	 * ilHtmlToPdfTransformerFactory constructor.
	 * @param $component
	 */
	public function __construct($component = '')
	{
		global $lng;

		$this->getValidEngines();

		$setting = 'pdf_transformer';
		if($component !== '')
		{
			$setting = $component . '_' . $setting;
		}
		$this->pdf_transformer_settings	= new ilSetting($setting);
		$this->lng	= $lng;
	}

	/**
	 * @return array
	 */
	public function getActivePdfTransformers()
	{
		$elements = array();
		/* @var ilHtmlToPdfTransformer $engine */
		foreach(self::$valid_engines as $engine)
		{
			if($engine->isActive())
			{
				$elements[$engine->getId()] = $this->lng->txt($engine->getTitle());
			}
		}
		return $elements;
	}

	/**
	 * @param $src
	 * @param $output
	 * @param $delivery_type
	 */
	public function deliverPDFFromHTMLFile($src, $output, $delivery_type)
	{
		$class_name = $this->pdf_transformer_settings->get('selected_transformer');
		$this->transformer = new $class_name;
		if($this->transformer->isActive())
		{
			$this->transformer->createPDFFileFromHTMLFile($src, $output);
			self::deliverPDF($output, $delivery_type);
		}
	}

	/**
	 * @param $src
	 * @param $output
	 * @param $delivery_type
	 */
	public function deliverPDFFromHTMLString($src, $output, $delivery_type)
	{
		$class_name = $this->pdf_transformer_settings->get('selected_transformer');
		$this->transformer = new $class_name;
		if($this->transformer->isActive())
		{
			$this->transformer->createPDFFileFromHTMLString($src, $output);
			self::deliverPDF($output, $delivery_type);
		}
	}

	/**
	 * @param $file
	 * @param $delivery_type
	 * @return mixed
	 */
	protected function deliverPDF($file, $delivery_type)
	{
		if(file_exists($file))
		{
			if(strtoupper($delivery_type) === self::PDF_OUTPUT_DOWNLOAD)
			{
				ilUtil::deliverFile($file, basename($file), '', false, true);
			}
			else if(strtoupper($delivery_type) === self::PDF_OUTPUT_INLINE)
			{
				ilUtil::deliverFile($file, basename($file), '', true, true);
			}
			return $file;
		}
		return false;
	}
	
	/**
	 * @param $src
	 * @param $output
	 * @param $delivery_type
	 */
	public function deliverPDFFromFilesArray($src, $output, $delivery_type)
	{
		$class_name = $this->pdf_transformer_settings->get('selected_transformer');
		$this->transformer = new $class_name;
		if($this->transformer->isActive())
		{
			if(is_array($src) && $this->transformer->supportMultiSourcesFiles())
			{
				$this->transformer->createPDFFileFromHTMLFile($src, $output);
			}
			else
			{
				$this->transformer->createPDFFileFromHTMLFile($this->createOneFileFromArray($src), $output);
			}
			self::deliverPDF($output, $delivery_type);
		}
	}

	/**
	 * @param $output
	 */
	public function deliverTestingPDFFromTestingHTMLFile($output)
	{
		$class_name = $this->pdf_transformer_settings->get('selected_transformer');
		$this->transformer = new $class_name;
		if($this->transformer->isActive())
		{
			$this->transformer->createPDFFileFromHTMLFile($this->transformer->getPathToTestHTML(), $output);
			self::deliverPDF($output, self::PDF_OUTPUT_DOWNLOAD);
		}
	}

	/**
	 * @param array $src
	 * @return string
	 */
	protected function createOneFileFromArray(array $src)
	{
		$tmp_file = dirname(reset($src)) . '/complete_pages_overview.html';
		$html_content	= '';
		foreach($src as $filename)
		{
			if(file_exists($filename))
			{
				$html_content .= file_get_contents($filename);
			}
		}
		file_put_contents($tmp_file, $html_content);
		return $tmp_file;
	}
}