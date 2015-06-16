<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * GUI class for the workflow of object deletion
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * 
 * @ilCtrl_Calls ilObjectDeletionGUI: 
 *
 * @ingroup ServicesObject
 */
class ilObjectDeletionGUI
{
	private $parent_gui = null;
	private $container_ref_id = 0; 
	
	private $selected_objects = array();
	private $selected_filtered_objects = array();
	private $has_subobjects = FALSE;
	
	/**
	 * Constructor
	 * @global type $ilCtrl
	 * @global type $lng
	 */
	public function __construct($a_parent_gui, $a_container_ref_id = 0)
	{
		global $lng;
		
		$this->lng = $lng;
		$this->lng->loadLanguageModule('obj');

		$this->parent_gui = $a_parent_gui;
		$this->container_ref_id = $a_container_ref_id;

	}
	
	/**
	 * Control class handling
	 * @return 
	 */
	public function executeCommand()
	{
		global $ilCtrl;
		
		$this->initSelected();
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd('showSelection');
		
		switch($next_class)
		{
			default:
				$this->$cmd();
				break;
		}
	}
	
	
	/**
	 * Apply filter
	 * @return 
	 */
	protected function applyFilter()
	{
		include_once './Services/Object/classes/class.ilObjectDeletionTableGUI.php';
		$table = new ilObjectDeletionTableGUI($this,'showContainerConfirmation',$this->getContainerRefId());
		$table->init();
		$table->resetOffset();
		$table->writeFilterToSession();
		
		return $this->showContainerConfirmation();
	}

	/**
	 * Reset filter
	 * @return type
	 */
	protected function resetFilter()
	{
		include_once './Services/Object/classes/class.ilObjectDeletionTableGUI.php';
		$table = new ilObjectDeletionTableGUI($this,'showContainerConfirmation',$this->getContainerRefId());
		$table->init();
		$table->resetOffset();
		$table->resetFilter();
		
		return $this->showContainerConfirmation();
		
	}


	
	/**
	 * Get selected items
	 */
	public function getSelectedObjects()
	{
		return (array) $this->selected_objects;
	}
	
	public function setSelectedFilteredObjects($a_objects)
	{
		$this->selected_filtered_objects = $a_objects;
		ilSession::set('object_deletion_filter', $this->selected_filtered_objects);
	}
	
	/**
	 * Get selected (from table filtered) objects
	 * @return type
	 */
	public function getSelectedFilteredObjects()
	{
		return (array) ilSession::get('object_deletion_filter');
	}
	
	
	/**
	 * Get container GUI
	 * @return object
	 */
	public function getParentGUI()
	{
		return $this->parent_gui;
	}
	
	/**
	 * get container
	 * @return ilContainer
	 */
	public function getContainerRefId()
	{
		return $this->container_ref_id;
	}
	
	/**
	 * Cancel deletion
	 */
	protected function cancel()
	{
		ilSession::clear('saved_post');
		ilSession::clear('object_deletion_filter');
		$GLOBALS['ilCtrl']->returnToParent($this);
	}


	/**
	 * show selection of deleteable objects
	 */
	protected function showSelection()
	{
		if(!$this->has_subobjects)
		{
			return $this->showConfirmation();
		}
		// show advanced selection gui
		$this->showContainerConfirmation();
	}
	
	/**
	 * Show deletion confirmation screen
	 */
	protected function showConfirmation()
	{
		include_once './Services/Repository/classes/class.ilRepUtilGUI.php';
		$rep_util = new ilRepUtilGUI($this, '');
		$rep_util->showDeleteConfirmation($this->getSelectedObjects());
	}
	
	/**
	 * Show confirmation table
	 */
	protected function showContainerConfirmation()
	{
		include_once './Services/Object/classes/class.ilObjectDeletionTableGUI.php';
		$table = new ilObjectDeletionTableGUI($this,'showContainerConfirmation',$this->getContainerRefId());
		$table->setObjects($this->getSelectedObjects());
		$table->init();
		$table->parse();
		
		$GLOBALS['tpl']->setContent($table->getHTML());
	}
	
	/**
	 * delete selected items
	 */
	protected function deleteSelectedItems()
	{
		if(!count((array) $_REQUEST['objects']))
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			return $this->showContainerConfirmation();
		}
		
		$this->doDelete((array) $_REQUEST['objects']);
		$GLOBALS['ilCtrl']->returnToParent($this);
	}
	
	/**
	 * delete all items
	 */
	protected function deleteAllItems()
	{
		$this->doDelete((array) $this->getSelectedFilteredObjects());
		$GLOBALS['ilCtrl']->returnToParent($this);
	}

		/**
	 * show simple delete confirmation
	 * @global type $ilSetting
	 * @global type $lng
	 */
	protected function confirmedDelete()
	{
		global $ilSetting, $lng;
		
		// TODO
		if(isset($_POST["mref_id"]))
		{
			$_SESSION["saved_post"] = array_unique(array_merge($_SESSION["saved_post"], $_POST["mref_id"]));
		}

		$this->doDelete($this->getSelectedObjects());

		$GLOBALS['ilCtrl']->returnToParent($this);
	}
	
	
	/**
	 * Perform deletion
	 */
	protected function doDelete($a_objects)
	{
		ilSession::set('saved_post', $a_objects);
		
		$GLOBALS['ilLog']->write(__METHOD__.' '.print_r($a_objects,TRUE));
		
		include_once './Services/Repository/classes/class.ilRepUtilGUI.php';
		$ru = new ilRepUtilGUI($this);
		$ru->deleteObjects((int) $this->getContainerRefId(), ilSession::get('saved_post'));
		ilSession::clear('saved_post');
	}
	
	
	
	
	
	/**
	 * Init selected objects for deletion
	 */
	protected function initSelected()
	{
		$new_selection = FALSE;
		
		// from item list gui
		if(isset($_GET['item_ref_id']))
		{
			$new_selection = TRUE;
			$this->selected_objects[] = (int) $_GET['item_ref_id'];
		}
		
		// from reputil confirmation
		if(is_array($_REQUEST['id']))
		{
			$new_selection = TRUE;
			foreach($_REQUEST['id'] as $id)
			{
				$this->selected_objects[] = (int) $id;
			}
		}
		
		if(!$new_selection)
		{
			$this->selected_objects = (array) ilSession::get('object_deletion');
		}
		else
		{
			ilSession::set('object_deletion', $this->selected_objects);
		}
		
		foreach($this->getSelectedObjects() as $ref_id)
		{
			if($a = $GLOBALS['tree']->getSubTreeTypes($ref_id,array('rolf')))
			{
				$this->has_subobjects = TRUE;
				break;
			}
		}
		
		
		
		return TRUE;
	}
}
?>