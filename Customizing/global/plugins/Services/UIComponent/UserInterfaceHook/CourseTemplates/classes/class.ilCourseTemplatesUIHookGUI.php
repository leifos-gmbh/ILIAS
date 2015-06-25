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
												
			return array("mode" => ilUIHookPluginGUI::APPEND, "html" => $drop->getHTML());
		}
		
		return array("mode" => ilUIHookPluginGUI::KEEP, "html" => "");
	}
}

?>