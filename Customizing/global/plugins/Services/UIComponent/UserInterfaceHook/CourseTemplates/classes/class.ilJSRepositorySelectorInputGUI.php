<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/Explorer2/classes/class.ilExplorerSelectInputGUI.php");

/**
 * Select repository nodes input GUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ilCtrl_IsCalledBy ilJSRepositorySelectorInputGUI: ilFormPropertyDispatchGUI
 */
class ilJSRepositorySelectorInputGUI extends ilExplorerSelectInputGUI
{
	/**
	 * Constructor
	 *
	 * @param	string	$a_title	Title
	 * @param	string	$a_postvar	Post Variable
	 */
	function __construct($title, $a_postvar)
	{
		global $lng, $ilCtrl;
		
		include_once("./Services/Repository/classes/class.ilRepositoryExplorerGUI.php");
		$ilCtrl->setParameterByClass("ilformpropertydispatchgui", "postvar", $a_postvar);
		$this->explorer_gui = new ilRepositoryExplorerGUI(array("ilpropertyformgui", "ilformpropertydispatchgui", "iljsrepositoryselectorinputgui"), $this->getExplHandleCmd());
		$this->explorer_gui->setSelectMode($a_postvar."_sel");
		
		$this->explorer_gui->setTypeWhiteList(array("cat", "root"));

		parent::__construct($title, $a_postvar, $this->explorer_gui);
		$this->setType("repository_select");	
	}
	
	function getTitleForNodeId($a_id)
	{
		return ilObject::_lookupTitle(ilObject::_lookupObjId($a_id));
	}
}
