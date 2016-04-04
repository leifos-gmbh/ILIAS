<?php

/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/PDFGeneration/classes/class.ilHtmlToPdfTransformerFactory.php';
require_once 'Services/PDFGeneration/classes/class.ilWebkitHtmlToPdfTransformerGUI.php';
require_once 'Services/PDFGeneration/classes/class.ilPhantomJsHtmlToPdfTransformerGUI.php';
require_once 'Services/PDFGeneration/classes/class.ilTCPDFHtmlToPdfTransformerGUI.php';
require_once 'Services/PDFGeneration/classes/class.ilFopHtmlToPdfTransformerGUI.php';

/**
 * Class ilHtmlToPdfTransformerGUIFactory
 */
class ilHtmlToPdfTransformerGUIFactory
{

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilHtmlToPdfTransformerFactory
	 */
	protected $transformer_factory;

	/**
	 * ilHtmlToPdfTransformerGUIFactory constructor.
	 */
	public function __construct()
	{
		global $lng, $ilCtrl;
		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->transformer_factory = new ilHtmlToPdfTransformerFactory();
	}

	/**
	 * @param ilTabsGUI $tabs_gui
	 */
	public function addTabs($tabs_gui)
	{
		foreach(ilHtmlToPdfTransformerFactory::$valid_engines as $class =>  $engine)
		{
			/* @var ilHtmlToPdfTransformer $engine */
			$tabs_gui->addTab( $class, $this->lng->txt($engine->getTitle()),
				$this->ctrl->getLinkTargetByClass('ilObjPDFGenerationGUI', 'view') . '&pdf_transformer=' . $class );
		}
	}

	/**
	 * @param $form
	 * @param ilHtmlToPdfTransformerGUI $transformer
	 */
	public function buildForm($form, $transformer)
	{
		/* @var ilHtmlToPdfTransformerGUI $transformer */
		$transformer = new $transformer;
		$transformer->populateForm();
		$transformer->appendForm($form);
		$transformer->appendHiddenTransformerNameToForm($form);
	}

	/**
	 * @param ilHtmlToPdfTransformer $transformer
	 */
	public function saveForm($transformer)
	{
		/* @var ilHtmlToPdfTransformerGUI $transformer */
		$transformer = new $transformer;
		if($transformer->checkForm())
		{
			$transformer->saveForm();
			ilUtil::sendSuccess($this->lng->txt("settings_saved"),true);
		}
	}
}