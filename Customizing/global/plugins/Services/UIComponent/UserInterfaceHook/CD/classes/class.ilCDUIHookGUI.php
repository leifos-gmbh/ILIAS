<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/classes/class.ilUIHookPluginGUI.php");

/**
 * User interface hook class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_IsCalledBy ilCDUIHookGUI: ilObjSystemFolderGUI, ilUIHookPluginGUI, ilObjCourseGUI
 * @ilCtrl_Calls ilCDUIHookGUI: cdCenterGUI, cdCompanyGUI, cdDesktopGUI, cdCourseTypesGUI
 * @ilCtrl_Calls ilCDUIHookGUI: cdParticipationConfirmationGUI, cdParticipantEvaluationGUI, ilCDTrainerGUI
 */
class ilCDUIHookGUI extends ilUIHookPluginGUI
{
	/**
	 * Modify user interface, paramters contain classes that can be modified
	 *
	 * @param
	 * @return
	 */
	function modifyGUI($a_comp, $a_part, $a_par = array())
	{
		global $rbacreview, $ilUser, $ilCtrl;
		switch ($a_comp)
		{
			case "Modules/SystemFolder":
				if ($a_part == "tabs")
				{
					$tabs = $a_par["tabs"];
					$tabs->addTab("center",
						$this->getPluginObject()->txt("manage_center"),
						$ilCtrl->getLinkTargetByClass(array("ilobjsystemfoldergui", "ilcduihookgui",
							"cdcentergui"), "listCenters"));

					$tabs->addTab("course_types",
						$this->getPluginObject()->txt("manage_course_types"),
						$ilCtrl->getLinkTargetByClass(array("ilobjsystemfoldergui", "ilcduihookgui",
							"cdcoursetypesgui"), "listCourseTypes"));

					$tabs->addTab("part_confirmation",
						$this->getPluginObject()->txt("participation_confirmation"),
						$ilCtrl->getLinkTargetByClass(array("ilobjsystemfoldergui", "ilcduihookgui",
							"cdparticipationconfirmationgui"), "listConfirmations"));

					//$tabs->removeTab("system_check");
					//var_dump($a_par["tabs"]->target);
					
					if ($ilCtrl->getCmdClass() == "cdcoursetypesgui")
					{
						$tabs->activateTab("course_types");
					}
					if ($ilCtrl->getCmdClass() == "cdcentergui")
					{
						$tabs->activateTab("center");
					}
					if ($ilCtrl->getCmdClass() == "cdparticipationconfirmationgui")
					{
						$tabs->activateTab("part_confirmation");
					}
				}
				break;
		}
	}

	/**
	 * Execute command
	 *
	 * @param
	 * @return
	 */
	function executeCommand()
	{
		global $ilCtrl, $tpl, $ilTabs;
		
		if ($_GET["forwardTo"] != "")
		{
			$ilCtrl->redirectByClass($_GET["forwardTo"]);
		}

		$next_class = $ilCtrl->getNextClass();

		$pl = $this->getPluginObject();

		switch ($next_class)
		{
			case "cdcentergui":
				$ilTabs->activateTab("center");
				$pl->includeClass("class.cdCenterGUI.php");
				$center_gui = new cdCenterGUI($pl);
				$ilCtrl->forwardCommand($center_gui);
				break;

			case "cdcoursetypesgui":
				$ilTabs->activateTab("course_types");
				$pl->includeClass("class.cdCourseTypesGUI.php");
				$course_types_gui = new cdCourseTypesGUI($pl);
				$ilCtrl->forwardCommand($course_types_gui);
				break;

			case "cdcompanygui":
				$tpl->getStandardTemplate();
				$pl->includeClass("class.cdCompanyGUI.php");
				$company_gui = new cdCompanyGUI($pl);
				$ilCtrl->forwardCommand($company_gui);
				$tpl->show();
				break;

			case "cddesktopgui":
				$tpl->getStandardTemplate();
				$pl->includeClass("class.cdDesktopGUI.php");
				$desk_gui = new cdDesktopGUI($pl);
				$ilCtrl->forwardCommand($desk_gui);
				break;

			case "cdparticipationconfirmationgui":
				//$tpl->getStandardTemplate();
				$ilTabs->activateTab("part_confirmation");
				//$ilTabs->activateTab("course_types");
				$pl->includeClass("class.cdParticipationConfirmationGUI.php");
				$conf_gui = new cdParticipationConfirmationGUI($pl);
				$ilCtrl->forwardCommand($conf_gui);
				break;
				
			case "cdcourseextgui":
				$pl->includeClass("class.cdCourseExtGUI.php");
				$course_ext_gui = new cdCourseExtGUI($pl);
				$ilCtrl->forwardCommand($course_ext_gui);
				break;
				
			case "cdparticipantevaluationgui":
				$pl->includeClass("class.cdParticipantEvaluationGUI.php");
				$pe_gui = new cdParticipantEvaluationGUI($pl);
				$ilCtrl->forwardCommand($pe_gui);
				break;

			case "ilcdtrainergui":
				include_once("./Services/CD/classes/class.ilCDTrainerGUI.php");
				$tr_gui = new ilCDTrainerGUI($pl);
				$ilCtrl->forwardCommand($tr_gui);
				break;
		}

	}

	/**
	 * Get html for ui area
	 *
	 * @param
	 * @return
	 */
	function getHTML($a_comp, $a_part, $a_par = array())
	{
		global $ilCtrl;

		if ($a_comp == "Services/MainMenu" && $a_part == "main_menu_list_entries")
		{
			return $this->replaceMainMenuListEntries($a_par);
		}

		// this redirect adjustement works together with the patch in
		// Services/User/classes/class.ilUserPasswordResetRequestTargetAdjustmentCase.php
		if ($a_comp == "Services/Utilities" && $a_part == "redirect")
		{
			if (is_int(strpos($a_par["html"], "cmd=showPassword")) &&
				is_int(strpos($a_par["html"], "baseClass=ilPersonalDesktopGUI")))
			{
				$_GET["baseClass"] = "ilUIHookPluginGUI";
				$target = $ilCtrl->getLinkTargetByClass(array('iluihookplugingui', 'ilcduihookgui', 'cddesktopgui', 'ilpersonalsettingsgui'), 'showPassword', '', false, false);
				//ilias.php?cmd=showPassword&cmdClass=ilpersonalsettingsgui&cmdNode=q:rs:rf:7&baseClass=ilUIHookPluginGUI
				return array("mode" => ilUIHookPluginGUI::REPLACE, "html" => $target);
			}
		}

		// this redirect adjustement works together with the patch in
		// Services/User/classes/class.ilUserProfileIncompleteRequestTargetAdjustmentCase.php
		if ($a_comp == "Services/Utilities" && $a_part == "redirect")
		{
			if (is_int(strpos($a_par["html"], "cmd=showPersonalData")) &&
				is_int(strpos($a_par["html"], "baseClass=ilPersonalDesktopGUI")))
			{
				$_GET["baseClass"] = "ilUIHookPluginGUI";
				$target = $ilCtrl->getLinkTargetByClass(array('iluihookplugingui', 'ilcduihookgui', 'cddesktopgui', 'ilpersonalprofilegui'), 'showPersonalData', '', false, false);
				//ilias.php?cmd=showPassword&cmdClass=ilpersonalsettingsgui&cmdNode=q:rs:rf:7&baseClass=ilUIHookPluginGUI
				return array("mode" => ilUIHookPluginGUI::REPLACE, "html" => $target);
			}
		}


		return array("mode" => ilUIHookPluginGUI::KEEP, "html" => "");
	}

	/**
	 * Replace main menu list entries
	 *
	 * @param
	 * @return
	 */
	function replaceMainMenuListEntries($a_par)
	{
		global $ilCtrl, $lng, $tree, $ilUser, $ilSetting, $ilAccess;

		$tpl = $this->getPluginObject()->getTemplate("tpl.cd_main_menu_list_entries.html");

		if ($_GET["baseClass"] == "ilUIHookPluginGUI")
		{
			$a_par["main_menu_gui"]->setActive("cd_desktop");
			if (strtolower($ilCtrl->getCmdClass()) == "cdcompanygui")
			{
				$a_par["main_menu_gui"]->setActive("companies");
			}
		}

		// standard entries
		$a_par["main_menu_gui"]->renderMainMenuListEntries($tpl, false);

		// cd desktop
		$tpl->setCurrentBlock("cd_desktop");
		if ($_GET["baseClass"] == "ilUIHookPluginGUI" && strtolower($ilCtrl->getCmdClass()) != "cdcompanygui"
			&& strtolower($ilCtrl->getCmdClass()) != "ilcdtrainergui")
		{
			$tpl->setVariable("MM_CLASS", "MMActive");
		}
		else
		{
			$tpl->setVariable("MM_CLASS", "MMInactive");
		}
		$tpl->setVariable("TXT_CD_DESKTOP", $lng->txt("personal_desktop"));
		$tpl->setVariable("SCRIPT_CD_DESKTOP",
			"ilias.php?baseClass=ilUIHookPluginGUI&amp;cmd=setCmdClass&amp;cmdClass=ilcduihookgui&amp;forwardTo=cdDesktopGUI");
		$tpl->parseCurrentBlock();

		// material
		$in_mat = false;
		if ($ilSetting->get("cd_mat_ref_id") > 0 && $ilAccess->checkAccess("read", "", $ilSetting->get("cd_mat_ref_id")))
		{
			if ($_GET["ref_id"] > 0)
			{
				$path = $tree->getPathId((int) $_GET["ref_id"]);
				if (in_array($ilSetting->get("cd_mat_ref_id"), $path))
				{
					$in_mat = true;
				}
			}
			include_once('Services/Link/classes/class.ilLink.php');
			$tpl->setCurrentBlock("cd_material");
			if ($a_par["main_menu_gui"]->active == "" && $in_mat)
			{
				$tpl->setVariable("MM_CLASS", "MMActive");
			}
			else
			{
				$tpl->setVariable("MM_CLASS", "MMInactive");
			}
			$tpl->setVariable("TXT_CD_MATERIAL",
				$this->getPluginObject()->txt("material"));
			$tpl->setVariable("SCRIPT_CD_MATERIAL",
				ilLink::_getStaticLink($ilSetting->get("cd_mat_ref_id"),'cat',true));
			$tpl->parseCurrentBlock();
		}

		// cd repository
		include_once("./Services/CD/classes/class.ilCDPermWrapper.php");
		$user_admin_centers = ilCDPermWrapper::getAdminCenters();
		if ($ilAccess->checkAccess("visible", "", ROOT_FOLDER_ID)
			&& is_array($user_admin_centers) && count($user_admin_centers) > 0)
		{
			include_once('Services/Link/classes/class.ilLink.php');
			$nd = $tree->getNodeData(ROOT_FOLDER_ID);
			$title = $nd["title"];
			if ($title == "ILIAS")
			{
				$title = $lng->txt("repository");
			}
			$tpl->setCurrentBlock("cd_repository");
			if ($a_par["main_menu_gui"]->active == "" && !$in_mat)
			{
				$tpl->setVariable("MM_CLASS", "MMActive");
			}
			else
			{
				$tpl->setVariable("MM_CLASS", "MMInactive");
			}
			$tpl->setVariable("TXT_CD_REPOSITORY", $title);
			$tpl->setVariable("SCRIPT_CD_REPOSITORY",
				ilLink::_getStaticLink(1,'root',true));
			$tpl->parseCurrentBlock();
		}

		// cd companies
		if (is_array($user_admin_centers) && count($user_admin_centers) > 0)
		{
			$tpl->setCurrentBlock("cd_companies");
			if (strtolower($ilCtrl->getCmdClass()) == "cdcompanygui")
			{
				$tpl->setVariable("MM_CLASS", "MMActive");
			}
			else
			{
				$tpl->setVariable("MM_CLASS", "MMInactive");
			}
			$tpl->setVariable("TXT_COMPANIES", $this->getPluginObject()->txt("companies"));
			$tpl->setVariable("SCRIPT_COMPANIES",
				"ilias.php?baseClass=ilUIHookPluginGUI&amp;cmd=setCmdClass&amp;cmdClass=ilcduihookgui&amp;forwardTo=cdCompanyGUI");
			$tpl->parseCurrentBlock();
		}
		else
		{
			// participants list for kunde-per roles
			if (ilCDPermWrapper::isPerRole())
			{
				$tpl->setCurrentBlock("cd_participants");
				if (strtolower($ilCtrl->getCmdClass()) == "cdcompanygui")
				{
					$tpl->setVariable("MM_CLASS", "MMActive");
				}
				else
				{
					$tpl->setVariable("MM_CLASS", "MMInactive");
				}
				$tpl->setVariable("TXT_PARTICIPANTS", $this->getPluginObject()->txt("participants"));
				$tpl->setVariable("SCRIPT_PARTICIPANTS",
					"ilias.php?baseClass=ilUIHookPluginGUI&amp;cmd=setCmdClass&amp;cmdClass=ilcduihookgui&amp;forwardTo=cdCompanyGUI");
				$tpl->parseCurrentBlock();
			}
		}

		// trainer
		include_once("./Services/CD/classes/class.ilCDPermWrapper.php");
		$user_admin_centers = ilCDPermWrapper::getAdminCenters();
		if (is_array($user_admin_centers) && count($user_admin_centers) > 0)
		{
			$tpl->setCurrentBlock("cd_trainers");
			if (strtolower($ilCtrl->getCmdClass()) == "ilcdtrainergui")
			{
				$tpl->setVariable("MM_CLASS", "MMActive");
			}
			else
			{
				$tpl->setVariable("MM_CLASS", "MMInactive");
			}
			$tpl->setVariable("TXT_TRAINERS", $this->getPluginObject()->txt("trainers"));
			$tpl->setVariable("SCRIPT_TRAINERS",
				"ilias.php?baseClass=ilUIHookPluginGUI&amp;cmd=setCmdClass&amp;cmdClass=ilcduihookgui&amp;forwardTo=ilCDTrainerGUI");
			$tpl->parseCurrentBlock();
		}

		$html = $tpl->get();
		return array("mode" => ilUIHookPluginGUI::REPLACE, "html" => $html);
	}


}
?>
