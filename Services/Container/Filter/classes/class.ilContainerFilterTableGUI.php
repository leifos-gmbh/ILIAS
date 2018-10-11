<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Filter admin table
 *
 * @author @leifos.de
 *
 * @ingroup
 */
class ilContainerFilterTableGUI extends ilTable2GUI
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
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $DIC;

		$this->id = "";
		$this->lng = $DIC->language();
		$this->ctrl = $DIC->ctrl();

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setData($this->getItems());
		$this->setTitle($this->lng->txt(""));

		$this->addColumn($this->lng->txt("cont_type_of_metadata_set"));
		$this->addColumn($this->lng->txt("cont_type_of_metadata_set"));
		$this->addColumn($this->lng->txt("actions"));

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.cont_filter_row.html", "");

		$this->addMultiCommand("", $this->lng->txt(""));
		$this->addCommandButton("", $this->lng->txt(""));
	}

	/**
	 * Get items
	 *
	 * @return array[]
	 */
	protected function getItems()
	{
		$items = [];

		return $items;
	}

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		$tpl = $this->tpl;
		$ctrl = $this->ctrl;
		$lng = $this->lng;

		$tpl->setVariable("ID", $a_set["id"]);
	}
}