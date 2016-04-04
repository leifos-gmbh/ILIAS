<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Object/classes/class.ilObject2GUI.php';
require_once 'Services/PDFGeneration/classes/class.ilHtmlToPdfTransformerFactory.php';
require_once 'Services/PDFGeneration/classes/class.ilHtmlToPdfTransformerGUIFactory.php';
require_once 'Services/PDFGeneration/classes/class.ilPDFGeneratorUtils.php';
/**
 * Class ilObjPDFGenerationGUI
 * @author Michael Jansen <mjansen@databay.de>
 * @ilCtrl_Calls ilObjPDFGenerationGUI: ilPermissionGUI
 * @ilCtrl_IsCalledBy ilObjPDFGenerationGUI : ilAdministrationGUI
 */
class ilObjPDFGenerationGUI extends ilObject2GUI
{
	/**
	 * @var ilHtmlToPdfTransformerFactory
	 */
	protected $transformer_factory;

	/**
	 * @var ilHtmlToPdfTransformerGUIFactory
	 */
	protected $transformer_gui_factory;
	
	protected $active_tab;
	
	protected $pdf_transformer_settings;
	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	
	/**
	 * @param int $a_id
	 * @param int $a_id_type
	 * @param int $a_parent_node_id
	 */
	public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
	{
		global $ilToolbar, $ilCtrl;
		/** @var $ilias ILIAS */
		parent::__construct($a_id, $a_id_type, $a_parent_node_id);
		$this->lng->loadLanguageModule('pdfgen');
		$this->transformer_factory		= new ilHtmlToPdfTransformerFactory();
		$this->transformer_gui_factory	= new ilHtmlToPdfTransformerGUIFactory();
		$this->pdf_transformer_settings	= new ilSetting('pdf_transformer');
		$this->toolbar 					= $ilToolbar;
		$this->ctrl 					= $ilCtrl;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getType()
	{
		return 'pdfg';
	}

	/**
	 * {@inheritdoc}
	 */
	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass();
		$cmd        = $this->ctrl->getCmd();
		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				require_once 'Services/AccessControl/classes/class.ilPermissionGUI.php';
				$perm_gui = new ilPermissionGUI($this);
				$this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				if($cmd == '' || $cmd == 'view')
				{
					$cmd = 'configForm';
				}
				$this->$cmd();
				break;
		}
	}

	public function configForm()
	{
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, 'view'));
		$transformer = ilUtil::stripSlashes($_GET['pdf_transformer']);
		if($transformer == '')
		{
			$active_transformers = $this->transformer_factory->getActivePdfTransformers();
			if(sizeof($active_transformers) > 0)
			{
				require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
				$button = ilLinkButton::getInstance();
				$button->setCaption('create_test_pdf');
				$button->setUrl($this->ctrl->getLinkTarget($this, 'createTestPdf'));
				$this->toolbar->addButtonInstance($button);
				$form->setTitle($this->lng->txt('pdf_config'));
				$selectPDF = new ilSelectInputGUI($this->lng->txt("select_pdf_generator"), "select_pdf_generator");
				$selectPDF->setOptions($active_transformers);
				$selectPDF->setValue($this->getSelectedTransformer());
				$form->addItem($selectPDF);
			}
			$this->active_tab = 'settings';
		}
		else
		{
			$this->transformer_gui_factory->buildForm($form, $transformer . 'GUI');
			$this->showInfoButton($transformer);
			$this->active_tab = $transformer;
		}

		$form->addCommandButton("saveSettings", $this->lng->txt("save"));
		$form->addCommandButton("view", $this->lng->txt("cancel"));
		$this->tpl->setContent($form->getHTML());
		$this->setActiveTab();
	}

	/**
	 * @param $transformer
	 */
	public function showInfoButton($transformer)
	{
		$transformer_instance = new $transformer;
		if($transformer_instance->hasInfoInterface())
		{
			require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
			$button = ilLinkButton::getInstance();
			$button->setCaption('show_info');
			$button->setUrl($this->ctrl->getLinkTarget($this, 'showInfo&pdf_transformer=' . $transformer));
			$this->toolbar->addButtonInstance($button);
		}
	}

	public function saveSettings()
	{
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();

		$transformer = ilUtil::stripSlashes($_POST['transformer']);
		if($transformer == '')
		{
			$form->setTitle($this->lng->txt('pdf_config'));
			$this->setSelectedTransformer(ilUtil::stripSlashes($_POST['select_pdf_generator']));
			$this->ctrl->redirect($this, "view");
		}
		else
		{
			$this->transformer_gui_factory->saveForm($transformer . 'GUI');
			$this->ctrl->redirect($this, "view" . '&pdf_transformer=' . $transformer);
		}
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getAdminTabs(ilTabsGUI $tabs_gui)
	{
		if($this->checkPermissionBool('read'))
		{
			$tabs_gui->addTarget('settings', $this->ctrl->getLinkTarget($this, 'view'), array(), __CLASS__);
		}

		$this->transformer_gui_factory->addTabs($tabs_gui);

		if($this->checkPermissionBool('edit_permission'))
		{
			$tabs_gui->addTarget('perm_settings',
				$this->ctrl->getLinkTargetByClass('ilpermissiongui', 'perm'),
				array(), 'ilpermissiongui');
		}

	}
	
	protected function setActiveTab()
	{
		global $ilTabs;
		$ilTabs->setTabActive($this->active_tab);
	}

	/**
	 * @return string
	 */
	protected function getSelectedTransformer()
	{
		return $this->pdf_transformer_settings->get('selected_transformer');
	}

	/**
	 * @param $selected_transformer
	 */
	protected function setSelectedTransformer($selected_transformer)
	{
		$this->pdf_transformer_settings->set('selected_transformer', $selected_transformer);
	}
	
	/*
	 * 
	 */
	protected function createTestPdf()
	{
		$dir		= ilPDFGeneratorUtils::getTestPdfDir();
		$factory	= new ilHtmlToPdfTransformerFactory();
		$factory->deliverTestingPDFFromTestingHTMLFile($dir. 'test.pdf');
	}

	protected function showInfo()
	{
		$transformer = ilUtil::stripSlashes($_GET['pdf_transformer']);
		$transformer_instance = new $transformer;
		ilUtil::sendInfo($transformer_instance->showInfo());
		$this->configForm();
	}
}