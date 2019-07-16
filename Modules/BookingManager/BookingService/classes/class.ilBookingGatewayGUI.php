<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BookingManager;

/**
 * This class is used for inegration of the booking manager as a service
 * into other repository objects, e.g. courses.
 *
 * @ilCtrl_Calls ilBookingGatewayGUI: ilPropertyFormGUI, ilBookingObjectServiceGUI, ilBookingReservationsGUI
 * @author killing@leifos.de
 * @ingroup ModulesBookingManager
 */
class ilBookingGatewayGUI
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
	 * @var ilObjectGUI
	 */
	protected $parent_gui;

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;

	/**
	 * @var int
	 */
	protected $obj_id;

	/**
	 * @var int
	 */
	protected $ref_id;

	/**
	 * @var ilObjBookingServiceSettings
	 */
	protected $current_settings;

	/**
	 * @var int
	 */
	protected $current_pool_ref_id;

	/**
	 * Constructor
	 */
	public function __construct(ilObjectGUI $parent_gui)
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->main_tpl = $DIC->ui()->mainTemplate();
		$this->parent_gui = $parent_gui;
		$this->tabs = $DIC->tabs();

		$this->lng->loadLanguageModule("book");

		$this->obj_id = (int) $parent_gui->object->getId();
		$this->ref_id = (int) $parent_gui->object->getRefId();

		$this->use_book_repo = new ilObjUseBookDBRepository($DIC->database());

		// get current settings
		$handler = new BookingManager\getObjectSettingsCommandHandler(
			new BookingManager\getObjectSettingsCommand($this->obj_id),
			$this->use_book_repo
		);
		$this->current_settings = $handler->handle()->getSettings();

		$this->initPool();
	}

	/**
	 * Init pool. Determin the current pool in $this->current_pool_ref_id.
	 *
	 * Host objects (e.g. courses) may use multiple booking pools. This method determines the current selected
	 * pool (stored in request parameter "pool_ref_id") within the host object user interface.
	 *
	 * If no pool has been selected yet, the first one attached to the host object is choosen.
	 *
	 * If no pools are attached to the host object at all we get a 0 ID.
	 */
	protected function initPool()
	{
		$ctrl = $this->ctrl;

		$ctrl->saveParameter($this, "pool_ref_id");
		$pool_ref_id  = (int) $_GET["pool_ref_id"];

		$book_ref_ids = $this->use_book_repo->getUsedBookingPools(ilObject::_lookupObjId($this->ref_id));

		if (!in_array($pool_ref_id, $book_ref_ids))
		{
			if (count($book_ref_ids) > 0)
			{
				$pool_ref_id = current($book_ref_ids);
			}
			else
			{
				$pool_ref_id = 0;
			}
		}
		$this->current_pool_ref_id = $pool_ref_id;
		if ($this->current_pool_ref_id > 0)
		{
			$ctrl->setParameter($this, "pool_ref_id", $this->current_pool_ref_id);
		}
	}

	/**
	 * Execute command
	 * @throws ilCtrlException
	 */
	function executeCommand()
	{
		$ctrl = $this->ctrl;

		$next_class = $ctrl->getNextClass($this);
		$cmd = $ctrl->getCmd("show");

		switch ($next_class)
		{
			case "ilpropertyformgui":
				$form = $this->initSettingsForm();
				$ctrl->setReturn($this, 'settings');
				$ctrl->forwardCommand($form);
				break;

			case "ilbookingobjectservicegui":
				$this->setSubTabs("book_obj");
				$book_ser_gui = new ilBookingObjectServiceGUI($this->ref_id,
					$this->current_pool_ref_id,
					$this->use_book_repo);
				$ctrl->forwardCommand($book_ser_gui);
				break;

			case "ilbookingreservationsgui":
				$this->setSubTabs("reservations");
				$pool = new ilObjBookingPool($this->current_pool_ref_id);
				$res_gui = new ilBookingReservationsGUI($pool);
				$this->ctrl->forwardCommand($res_gui);
				break;


			default:
				if (in_array($cmd, array("show", "settings", "saveSettings")))
				{
					$this->$cmd();
				}
		}
	}

	/**
	 * Set sub tabs
	 *
	 * @param $active
	 */
	protected function setSubTabs($active)
	{
		$tabs = $this->tabs;
		$ctrl = $this->ctrl;
		$lng = $this->lng;

		$tabs->addSubTab("book_obj",
			$lng->txt("book_objects"),
			$ctrl->getLinkTargetByClass("ilbookingobjectservicegui", ""));
		$tabs->addSubTab("reservations",
			$lng->txt("book_reservations"),
			$ctrl->getLinkTargetByClass("ilbookingreservationsgui", ""));
		$tabs->addSubTab("settings",
			$lng->txt("settings"),
			$ctrl->getLinkTarget($this, "settings"));

		$tabs->activateSubTab($active);
	}
	
	
	/**
	 * Show
	 */
	protected function show()
	{
		$ctrl = $this->ctrl;
		$ctrl->redirectByClass("ilbookingobjectservicegui");
	}

	//
	// Settings
	//

	/**
	 * Settings
	 */
	protected function settings()
	{
		$this->setSubTabs("settings");
		$main_tpl = $this->main_tpl;
		$form = $this->initSettingsForm();
		$main_tpl->setContent($form->getHTML());
	}

	/**
	 * Init settings form.
	 */
	public function initSettingsForm()
	{
		$ctrl = $this->ctrl;
		$lng = $this->lng;

		$form = new ilPropertyFormGUI();

		// booking tools
		$repo = new ilRepositorySelector2InputGUI($this->lng->txt("objs_book"), "booking_obj_ids", true);
		$repo->getExplorerGUI()->setSelectableTypes(["book"]);
		$repo->getExplorerGUI()->setTypeWhiteList(
			["book", "root", "cat", "grp", "fold", "crs"]
		);
		$form->addItem($repo);
		$repo->setValue($this->current_settings->getUsedBookingObjectIds());

		$form->addCommandButton("saveSettings", $lng->txt("save"));

		$form->setTitle($lng->txt("..."));
		$form->setFormAction($ctrl->getFormAction($this));

		return $form;
	}

	/**
	 * Save settings form
	 */
	public function saveSettings()
	{
		$ctrl = $this->ctrl;
		$lng = $this->lng;
		$main_tpl = $this->main_tpl;

		$form = $this->initSettingsForm();
		if ($form->checkInput())
		{
			$b_ids = $form->getInput("booking_obj_ids");
			$b_ids = is_array($b_ids)
				? array_map(function ($i) {
					return (int) $i;
				}, $b_ids)
				: [];
			$cmd = new BookingManager\saveObjectSettingsCommand(new ilObjBookingServiceSettings(
				$this->obj_id,
				$b_ids
			));

			$repo = $this->use_book_repo;
			$handler = new BookingManager\saveObjectSettingsCommandHandler($cmd, $repo);
			$handler->handle();

			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ctrl->redirect($this, "");
		}
		else
		{
			$form->setValuesByPost();
			$main_tpl->setContent($form->getHtml());
		}
	}

}