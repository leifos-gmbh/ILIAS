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
		global $ilCtrl;
		
		if ($a_comp == "Services/MainMenu" && 
			$a_part == "main_menu_list_entries")
		{	
			$pl = $this->getPluginObject();		
			$ct = $pl->getCourseTemplatesInstance();
			
			if(!$ct->getGlobalTemplateCategory())
			{
				return;
			}
			
			include_once "Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php";
			$drop = new ilAdvancedSelectionListGUI();
			$drop->setListTitle($pl->txt("main_menu_title"));
			$drop->setStyle(ilAdvancedSelectionListGUI::STYLE_LINK);
			$drop->setSelectionHeaderSpanClass("MMSpan");
			$drop->setHeaderIcon(ilAdvancedSelectionListGUI::ICON_ARROW);
			$drop->setItemLinkClass("small");
			$drop->setUseImages(false);
			
			$tmpl_url = $ilCtrl->getLinkTargetByClass(array("iluipluginroutergui", "ilcoursetemplateseditorgui"), "createTemplate");
			$drop->addItem($pl->txt("create_new_template"), "", $tmpl_url);
						
			if($ct->getAvailableTemplates())
			{
				$crs_url = $ilCtrl->getLinkTargetByClass(array("iluipluginroutergui", "ilcoursetemplateseditorgui"), "createCourse");		
				$drop->addItem($pl->txt("create_new_course"), "", $crs_url);
			}
			
			$mmle_tpl = new ilTemplate("tpl.main_menu_list_entries.html", true, true, "Services/MainMenu");
			$mmle_html = $a_par["main_menu_gui"]->renderMainMenuListEntries($mmle_tpl);
			$mmle_html = substr(trim($mmle_html), 0, -5); // remove closing ul
			
			// convert div to li
			$drop = $drop->getHTML();
			$drop = str_replace('<div class="btn-group">', '<li class="dropdown">', $drop);
			$drop = str_replace('</div>', '</li>', $drop);
			
			$html = $mmle_html.$drop."</ul>";
												
			return array("mode" => ilUIHookPluginGUI::REPLACE, "html" => $html);
		}
		else if($a_comp == "Services/Object" && 
			$a_part == "object_list_gui")
		{				
			// add course template info to course list gui
			$list_gui = $a_par["object_list_gui"];
			if(get_class($list_gui) == "ilObjCourseListGUI")
			{
				$pl = $this->getPluginObject();		
				$ct = $pl->getCourseTemplatesInstance();

				if(!$ct->getGlobalTemplateCategory())
				{
					return;
				}
				
				$obj_id = $a_par["obj_id"];
								
				// preload templates data
				if(!is_array(self::$template_data))
				{																
					self::$template_data["templates"] = $ct->getCoursesWithTemplateStatus();				
					self::$template_data["copies"] = $ct->getCoursesFromTemplate();
					self::$template_data["template_title"] = $pl->txt("course_list_gui_property_template");
					self::$template_data["copy_title"] = $pl->txt("course_list_gui_property_from_template");					
				}		

				// is valid course template?
				if(in_array($obj_id, self::$template_data["templates"]))
				{
					$list_gui->addCustomProperty(
						"", 
						self::$template_data["template_title"],
						true,
						true
					);							
				}

				// is course copied from template?
				if(array_key_exists($obj_id, self::$template_data["copies"]))
				{
					$list_gui->addCustomProperty(
						self::$template_data["copy_title"], 
						ilObject::_lookupTitle(self::$template_data["copies"][$obj_id]),
						false,
						true
					);			
				}				
			}			
		}
		
		return array("mode" => ilUIHookPluginGUI::KEEP, "html" => "");
	}
}

?>