<?php


/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Adapter for JsonRpc frontend, currently a GUI class
 * since the requests are routed through the whole ilCtrl
 * execution path.
 *
 * @author killing@leifos.de
 * @ingroup ServicesCOPage
 */
class ilPageEditorRpcAdapterGUI
{
	/**
	 * @var ilPageObjectGUI
	 */
	protected $page_gui;

	/**
	 * @var \ILIAS\DI\UIServices
	 */
	protected $ui;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * Constructor
	 */
	public function __construct(ilPageObjectGUI $page_gui, ilCtrl $ctrl, \ILIAS\DI\UIServices $ui)
	{
		$this->ctrl = $ctrl;
		$this->ui = $ui;
		$this->page_gui = $page_gui;
	}

	/**
	 * Execute command
	 */
	function executeCommand()
	{
		$ctrl = $this->ctrl;

		$next_class = $ctrl->getNextClass($this);
		$cmd = $ctrl->getCmd("invokeRpcServer");

		switch ($next_class)
		{
			default:
				if (in_array($cmd, array("invokeRpcServer")))
				{
					$this->$cmd();
				}
		}
	}

	/**
	 * Invoke rpc server
	 */
	protected function invokeRpcServer()
	{
		$server = new ilPageEditorRpcServer($this->page_gui, $this->ui);
		$server->getRequest();
		$server->reply();
	}

}