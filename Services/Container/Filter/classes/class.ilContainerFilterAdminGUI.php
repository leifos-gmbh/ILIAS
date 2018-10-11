<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Filter administration for containers
 *
 * @author killing@leifos.de
 * @ingroup ServicesContainer
 */
class ilContainerFilterAdminGUI
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
	 * @var ilTemplate
	 */
	protected $main_tpl;

	/**
	 * Constructor
	 */
	public function __construct(ilContainerGUI $container_gui)
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->main_tpl = $DIC->ui()->mainTemplate();
		$this->container_gui = $container_gui;
	}
	
	/**
	 * Execute command
	 */
	function executeCommand()
	{
		$ctrl = $this->ctrl;
		
		$next_class = $ctrl->getNextClass($this);
		$cmd = $ctrl->getCmd("show");
	
		switch ($next_class)
		{
			default:
				if (in_array($cmd, array("show")))
				{
					$this->$cmd();
				}
		}
	}

	/**
	 * Show table
	 */
	protected function show()
	{
		$main_tpl = $this->main_tpl;

		$table = new ilContainerFilterTableGUI($this, "show");
		$main_tpl->setContent($table-getHTML());
	}
	
}