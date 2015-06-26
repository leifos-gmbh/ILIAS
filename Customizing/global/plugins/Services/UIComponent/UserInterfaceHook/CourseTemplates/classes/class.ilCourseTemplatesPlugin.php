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
	
	public function initRepositorySelectorInput()
	{
		include_once $this->getClassesDirectory()."/class.ilJSRepositorySelectorInputGUI.php";
	}
}

?>
