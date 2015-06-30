<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/classes/class.ilUIHookPluginGUI.php");

/**
 * Course template user interface hook class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesUIComponent
 */
class ilCourseTemplatesUIHookGUI extends ilUIHookPluginGUI
{
	protected static $template_data; // [array]
	
	function getHTML($a_comp, $a_part, $a_par = array())
	{		
		$pl = $this->getPluginObject();		
		if(!$pl->isAccessible())
		{
			return;
		}
		
		if ($a_comp == "Services/MainMenu" && 
			$a_part == "main_menu_list_entries")
		{							
			$mmle_tpl = new ilTemplate("tpl.main_menu_list_entries.html", true, true, "Services/MainMenu");
			$mmle_html = $a_par["main_menu_gui"]->renderMainMenuListEntries($mmle_tpl);
			
			$html = substr(trim($mmle_html), 0, -5). // remove closing ul
				$this->renderMenu().
				"</ul>";
											
			return array("mode" => ilUIHookPluginGUI::REPLACE, "html" => $html);
		}
		else if($a_comp == "Services/Object" && 
			$a_part == "object_list_gui")
		{				
			// add course template info to course list gui
			$list_gui = $a_par["object_list_gui"];
			if(get_class($list_gui) == "ilObjCourseListGUI")
			{
				$this->renderListGUI($list_gui, $a_par["obj_id"]);
			}				
		}
		
		return array("mode" => ilUIHookPluginGUI::KEEP, "html" => "");
	}
		
	protected function renderMenu()
	{				
		$pl = $this->getPluginObject();		
		
		return '<li class="ilMainMenu" style="text-transform:uppercase;"><a href="'.$this->getMenuHref().'">'.$pl->txt("main_menu_title").'</a></li>';
	}	
	
	protected function renderListGUI(ilObjectListGUI $a_list_gui, $a_obj_id)
	{
		$pl = $this->getPluginObject();		
		$ct = $pl->getCourseTemplatesInstance();

		// preload templates data
		if(!is_array(self::$template_data))
		{																
			self::$template_data["templates"] = $ct->getCoursesWithTemplateStatus();				
			self::$template_data["copies"] = $ct->getCoursesFromTemplate();
			self::$template_data["template_title"] = $pl->txt("course_list_gui_property_template");
			self::$template_data["copy_title"] = $pl->txt("course_list_gui_property_from_template");					
		}		

		// is valid course template?
		if(in_array($a_obj_id, self::$template_data["templates"]))
		{
			$a_list_gui->addCustomProperty(
				"", 
				self::$template_data["template_title"],
				true,
				true
			);							
		}

		// is course copied from template?
		if(array_key_exists($a_obj_id, self::$template_data["copies"]))
		{
			$a_list_gui->addCustomProperty(
				self::$template_data["copy_title"], 
				ilObject::_lookupTitle(self::$template_data["copies"][$a_obj_id]),
				false,
				true
			);			
		}				
	}	
		
	public function getMenuHref()
	{
		global $ilCtrl;
		return $ilCtrl->getLinkTargetByClass(array("iluipluginroutergui", "ilcoursetemplateseditorgui"), "listTemplates");
	}	
}

?>