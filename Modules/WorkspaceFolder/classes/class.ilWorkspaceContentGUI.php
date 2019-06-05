<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Workspace content renderer
 *
 * @author @leifos.de
 * @ingroup
 */
class ilWorkspaceContentGUI
{
	/**
	 * @var int
	 */
	protected $current_node;

	/**
	 * @var bool
	 */
	protected $admin;

	/**
	 * @var object
	 */
	protected $access_handler;

	/**
	 * @var \ILIAS\DI\UIServices
	 */
	protected $ui;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilObjUser 
	 */
	protected $user;

	/**
	 * Constructor
	 */
	public function __construct($object_gui, int $node_id, bool $admin, $access_handler,
	                            \ILIAS\DI\UIServices $ui, ilLanguage $lng, ilObjUser $user)
	{
		$this->current_node = $node_id;
		$this->admin = $admin;
		$this->access_handler = $access_handler;
		$this->object_gui = $object_gui;
		$this->ui = $ui;
		$this->lng = $lng;
		$this->user = $user;
	}

	/**
	 * Render
	 *
	 */
	public function render()
	{
		$panel = $this->ui->factory()->panel()->standard($this->lng->txt("content"), []);

		return $this->ui->renderer()->render($panel);

		include_once "Modules/WorkspaceFolder/classes/class.ilObjWorkspaceFolderTableGUI.php";
		$table = new ilObjWorkspaceFolderTableGUI(
			$this->object_gui,
			"render",
			$this->current_node,
			$this->access_handler,
			$this->admin);
		return $table->getHTML();
	}

	/**
	 *
	 */
	protected function getItems()
	{
		$user = $this->user;

		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
		$tree = new ilWorkspaceTree($ilUser->getId());
		$nodes = $tree->getChilds($this->node_id, "title");

		if(sizeof($nodes))
		{
			include_once("./Services/Object/classes/class.ilObjectListGUIPreloader.php");
			$preloader = new ilObjectListGUIPreloader(ilObjectListGUI::CONTEXT_WORKSPACE);
			foreach($nodes as $node)
			{
				$preloader->addItem($node["obj_id"], $node["type"]);
			}
			$preloader->preload();
			unset($preloader);
		}

		$this->shared_objects = $this->access_handler->getObjectsIShare();

		return $nodes;
	}


}