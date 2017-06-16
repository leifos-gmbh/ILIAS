<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilLMEditShortTitlesGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilObjLearningModule
	 */
	protected $lm;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * Learning module
	 *
	 * @param ilObjLearningModule $a_lm learning module
	 */
	function __construct(ilObjLearningModule $a_lm)
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lm = $a_lm;
		$this->tpl = $DIC["tpl"];
		$this->lng = $DIC->language();

	}

	/**
	 * Execute command
	 */
	function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd("listShortTitles");

		switch ($next_class)
		{
			default:
				if (in_array($cmd, array("listShortTitles", "save")))
				{
					$this->$cmd();
				}
		}
	}

	/**
	 * List short titles
	 */
	function listShortTitles()
	{
		include_once("./Modules/LearningModule/classes/class.ilLMEditShortTitlesTableGUI.php");
		$tab = new ilLMEditShortTitlesTableGUI($this, "listShortTitles", $this->lm);
		$this->tpl->setContent($tab->getHTML());
	}

	/**
	 * Save short titles
	 */
	function save()
	{
		if (is_array($_POST["short_title"]))
		{
			foreach ($_POST["short_title"] as $id => $title)
			{
				if (ilLMObject::_lookupContObjID($id) == $this->lm->getId())
				{
					ilLMObject::writeShortTitle($id, ilUtil::stripSlashes($title));
				}
			}
		}
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		$this->ctrl->redirect($this, "listShortTitles");
	}
}