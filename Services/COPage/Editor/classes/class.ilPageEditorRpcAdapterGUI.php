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
	 * Constructor
	 */
	public function __construct()
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
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
		$server = new ilPageEditorRpcServer();
		$server->getRequest();
		$server->reply();
	}

}