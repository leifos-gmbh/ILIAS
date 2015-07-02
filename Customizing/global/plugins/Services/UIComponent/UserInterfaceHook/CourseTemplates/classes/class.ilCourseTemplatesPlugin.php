<?php

include_once("./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php");
 
/**
 * Course template user interface plugin
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 */
class ilCourseTemplatesPlugin extends ilUserInterfaceHookPlugin
{
	function getPluginName()
	{
		return "CourseTemplates";
	}
	
	public function isAccessible()
	{
		global $rbacsystem;
		
		// admin privileges
		if(!$rbacsystem->checkAccess("visible,read", SYSTEM_FOLDER_ID))
		{
			return false;
		}
				
		// plugin must have valid configuration
		$ct = $this->getCourseTemplatesInstance();
		if(!$ct->getGlobalTemplateCategory())
		{
			return false;
		}
		
		// has to be able to create courses in central template category
		return $rbacsystem->checkAccess("create_crs", $ct->getGlobalTemplateCategory());
	}
	
	/**
	 * Get instance of ilCourseTemplates
	 * 
	 * @return ilCourseTemplates
	 */
	public function getCourseTemplatesInstance()
	{
		include_once $this->getClassesDirectory()."/class.ilCourseTemplates.php";
		return ilCourseTemplates::getInstance($this->getId());
	}
	
	public function getUIHookGUI()
	{
		include_once $this->getClassesDirectory()."/class.ilCourseTemplatesUIHookGUI.php";
		$gui = new ilCourseTemplatesUIHookGUI();
		$gui->setPluginObject($this);
		return $gui;
	}
}

?>
