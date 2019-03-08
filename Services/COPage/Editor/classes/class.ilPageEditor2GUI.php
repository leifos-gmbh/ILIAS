<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Page editor
 *
 * @ilCtrl_Calls ilPageEditor2GUI: ilPageEditorRpcAdapterGUI
 * @author killing@leifos.de
 * @ingroup ServicesCOPage
 */
class ilPageEditor2GUI
{
	/**
	 * @var ilPageObjectGUI
	 */
	protected $page_gui;

	/**
	 * @var ilTemplate
	 */
	protected $main_tpl;


	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * Constructor
	 */
	public function __construct(ilPageObjectGUI $a_page_gui, ilCtrl $ctrl, ilTemplate $main_tpl)
	{
		$this->page_gui = $a_page_gui;
		$this->ctrl = $ctrl;
		$this->main_tpl = $main_tpl;
	}

	/**
	 * Execute command
	 * @throws ilCtrlException
	 */
	function executeCommand()
	{
		$ctrl = $this->ctrl;

		$next_class = $ctrl->getNextClass($this);
		$cmd = $ctrl->getCmd("showEditScreen");

		switch ($next_class)
		{
			case "ilpageeditorrpcadaptergui":
				$rpc_gui = new ilPageEditorRpcAdapterGUI();
				$ctrl->forwardCommand($rpc_gui);
				break;

			default:
				if (in_array($cmd, array("showEditScreen")))
				{
					$this->$cmd();
				}
		}
	}


	/**
	 * Show edit screen
	 */
	protected function showEditScreen()
	{
		$ctrl = $this->ctrl;
		$main_tpl = $this->main_tpl;

		$main_tpl->setContent("<div id='copg-editor-canvas'></div>");
		$main_tpl->setRightContent("<div id='copg-editor-slate'></div>");

		$main_tpl->addJavaScript("./Services/COPage/Editor/dist/Editor.js");
		$uri = $ctrl->getLinkTargetByClass(["ilPageEditorRpcAdapterGUI"], "", "", false, false);
		$main_tpl->addOnLoadCode("il.copg.editor.init('$uri');");
	}

}