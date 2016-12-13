<?php
/* Copyright (c) 1998-20016 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for LTI consumer listing
 *
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesUser
 */
class ilUserRoleStartingPointTableGUI extends ilTable2GUI
{
	protected $log;

	const TABLE_POSITION_USER_CHOOSES = -1;
	const TABLE_POSITION_DEFAULT = 9999;

	function __construct($a_parent_obj, $a_parent_cmd, $a_template_context)
	{
		global $ilCtrl, $lng;

		$this->log = ilLoggerFactory::getLogger("user");

		$this->setId("usrrolesp");

		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		$this->getItems();

		$this->setLimit(9999);
		$this->setTitle($lng->txt("user_role_starting_point"));

		$this->addColumn("#");
		$this->addColumn($lng->txt("criteria"));
		$this->addColumn($lng->txt("starting_page"));
		$this->addColumn($lng->txt("actions"));
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.user_role_starting_point_row.html", "Services/User");
		$this->addCommandButton("saveorder", $lng->txt("role_save_order"));

		$this->setExternalSorting(true);

		require_once "./Services/AccessControl/classes/class.ilObjRole.php";
		$roles_without_point = ilObjRole::getGlobalRolesWithoutStartingPoint();
		if(!empty($roles_without_point))
		{
			$this->addCommandButton("rolestartingpointform", $this->lng->txt('create_role_starting_point'));
		}
		else
		{
			ilUtil::sendInfo($lng->txt("all_roles_has_starting_point"));
		}

	}

	/**
	 * Get data
	 */
	function getItems()
	{
		global $lng;

		include_once "Services/User/classes/class.ilUserUtil.php";
		require_once "Services/Object/classes/class.ilObjectDataCache.php";
		require_once "Services/AccessControl/classes/class.ilObjRole.php";

		$dc = new ilObjectDataCache();

		$valid_points = ilUserUtil::getPossibleStartingPoints();

		$status = (ilUserUtil::hasPersonalStartingPoint()? "Individual" : "Default");

		$result = array();
		$result[] = array (
			"id" => "user",
			"criteria" => $lng->txt("user_chooses_starting_page"),
			"starting_page" => $status,
			"starting_position" => self::TABLE_POSITION_USER_CHOOSES
		);

		$roles = ilObjRole::getRolesWithStartingPoint();

		foreach ($roles as $g_role)
		{
			$role = new ilObjRole($g_role['role_id']);
			$starting_point = $role->getStartingPoint();
			$position = $role->getStartingPosition();
			$sp_text = $valid_points[$starting_point];

			if($starting_point == ilUserUtil::START_REPOSITORY_OBJ)
			{
				$reference_id = $role->getStartingObject();

				$object_id = ilObject::_lookupObjId($reference_id);
				$type = $dc->lookupType($object_id);
				$title = $dc->lookupTitle($object_id);
				$sp_text = $this->lng->txt("type").": ".$type." ".$this->lng->txt("ref_id")." ".$reference_id." ".$this->lng->txt("title")."<i>\"".$title."\"</i>";
			}

			$result[] = array (
				"id" => $role->getId(),
				"criteria" => $role->getTitle(),
				"starting_page" => $sp_text,
				"starting_position" => (int)$position
			);
		}

		$default_sp = ilUserUtil::getStartingPoint();
		$starting_point = $valid_points[$default_sp];
		if($default_sp == ilUserUtil::START_REPOSITORY_OBJ)
		{
			$reference_id = ilUserUtil::getStartingObject();

			$object_id = ilObject::_lookupObjId($reference_id);
			$type = $dc->lookupType($object_id);
			$title = $dc->lookupTitle($object_id);
			$starting_point = $this->lng->txt("type").": ".$type." ".$this->lng->txt("ref_id")." ".$reference_id." ".$this->lng->txt("title")."<i>\"".$title."\"</i>";
		}

		$result[] = array (
			"id" => "default",
			"criteria" => $lng->txt("default"),
			"starting_page" => $starting_point,
			"starting_position" => self::TABLE_POSITION_DEFAULT
		);

		$result = ilUtil::sortArray($result, "starting_position", "asc", false);

		$this->setData($result);

	}

	/**
	 * Fill a single data row.
	 */
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl;

		include_once "Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php";

		$list = new ilAdvancedSelectionListGUI();
		$list->setListTitle($lng->txt("actions"));

		if($a_set['id'] > 0 && $a_set['id'] != 'default' && $a_set['id'] != 'user')
		{
			$ilCtrl->setParameter($this->getParentObject(), "rolid", $a_set["id"]);

			$list->setId($a_set["id"]);

			$edit_url = $ilCtrl->getLinkTarget($this->getParentObject(), "rolestartingpointform");
			$list->addItem($lng->txt("edit"), "", $edit_url);
			$delete_url = $ilCtrl->getLinkTarget($this->getParentObject(), "confirmdeletestartingpoint");
			$list->addItem($lng->txt("delete"), "", $delete_url);
			$this->tpl->setVariable("VAL_ID", "position[".$a_set['id']."]");
			$this->tpl->setVariable("VAL_POS", $a_set["starting_position"]);

			$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("has_role").":".$a_set["criteria"]);
		}
		else
		{
			if($a_set['id'] == "default")
			{
				$ilCtrl->setParameter($this->getParentObject(), "rolid", "default");
				$edit_url = $ilCtrl->getLinkTarget($this->getParentObject(), "rolestartingpointform");
			}
			else
			{
				$ilCtrl->setParameter($this->getParentObject(), "rolid", "user");
				$edit_url = $ilCtrl->getLinkTarget($this->getParentObject(), "userstartingpointform");
			}

			$list->addItem($lng->txt("edit"), "", $edit_url);

			$this->tpl->setVariable("HIDDEN", "hidden");

			$this->tpl->setVariable("TXT_TITLE", $a_set["criteria"]);
		}

		$this->tpl->setVariable("TXT_PAGE", $a_set["starting_page"]);

		$this->tpl->setVariable("ACTION", $list->getHTML());
	}

}