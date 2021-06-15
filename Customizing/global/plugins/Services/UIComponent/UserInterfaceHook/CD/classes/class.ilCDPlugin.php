<?php

include_once("./Services/UIComponent/classes/class.ilUserInterfaceHookPlugin.php");
 
/**
 * User interface hook for hsu
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 */
class ilCDPlugin extends ilUserInterfaceHookPlugin
{
	function getPluginName()
	{
		return "CD";
	}
	
	/**
	 * Modify course toolbar
	 *
	 * @param object $a_ctb toolbar object
	 */
	function modifyCourseToolbar($a_ctb)
	{
		$this->includeClass("class.cdCourseExtGUI.php");
		cdCourseExtGUI::modifyCourseToolbar($a_ctb, $this);
	}
	
}

?>
