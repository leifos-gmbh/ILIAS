<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    public function modifyGUI($a_comp, $a_part, $a_par = array())
    {
        global $rbacreview, $ilUser, $ilCtrl;
        switch ($a_comp) {
            case "Modules/SystemFolder":
                if ($a_part == "tabs") {
                    $tabs = $a_par["tabs"];
                    $tabs->addTab(
                        "center",
                        $this->getPluginObject()->txt("manage_center"),
                        $ilCtrl->getLinkTargetByClass(array("ilobjsystemfoldergui", "ilcduihookgui",
                            "cdcentergui"), "listCenters")
                    );

                    $tabs->addTab(
                        "course_types",
                        $this->getPluginObject()->txt("manage_course_types"),
                        $ilCtrl->getLinkTargetByClass(array("ilobjsystemfoldergui", "ilcduihookgui",
                            "cdcoursetypesgui"), "listCourseTypes")
                    );

                    $tabs->addTab(
                        "part_confirmation",
                        $this->getPluginObject()->txt("participation_confirmation"),
                        $ilCtrl->getLinkTargetByClass(array("ilobjsystemfoldergui", "ilcduihookgui",
                            "cdparticipationconfirmationgui"), "listConfirmations")
                    );

                    //$tabs->removeTab("system_check");
                    //var_dump($a_par["tabs"]->target);
                    
                    if ($ilCtrl->getCmdClass() == "cdcoursetypesgui") {
                        $tabs->activateTab("course_types");
                    }
                    if ($ilCtrl->getCmdClass() == "cdcentergui") {
                        $tabs->activateTab("center");
                    }
                    if ($ilCtrl->getCmdClass() == "cdparticipationconfirmationgui") {
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
    public function executeCommand()
    {
        global $ilCtrl, $tpl, $ilTabs;
        
        if ($_GET["forwardTo"] != "") {
            $ilCtrl->redirectByClass($_GET["forwardTo"]);
        }

        $next_class = $ilCtrl->getNextClass();

        $pl = $this->getPluginObject();

        switch ($next_class) {
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
                $pl->includeClass("class.cdCompanyGUI.php");
                $company_gui = new cdCompanyGUI($pl);
                $ilCtrl->forwardCommand($company_gui);
                $tpl->printToStdOut();
                break;

            case "cddesktopgui":
                $pl->includeClass("class.cdDesktopGUI.php");
                $desk_gui = new cdDesktopGUI($pl);
                $ilCtrl->forwardCommand($desk_gui);
                break;

            case "cdparticipationconfirmationgui":
                $ilTabs->activateTab("part_confirmation");
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
    public function getHTML($a_comp, $a_part, $a_par = array())
    {
        global $ilCtrl;

        if ($a_comp == "Services/MainMenu" && $a_part == "main_menu_list_entries") {
            return $this->replaceMainMenuListEntries($a_par);
        }

        // this redirect adjustement works together with the patch in
        // Services/User/classes/class.ilUserPasswordResetRequestTargetAdjustmentCase.php
        if ($a_comp == "Services/Utilities" && $a_part == "redirect") {
            // part of the loop
            if (is_int(strpos($a_par["html"], "cmd=showPassword")) &&
                is_int(stripos($a_par["html"], "baseClass=ilDashboardGUI"))) {
                $_GET["baseClass"] = "ilUIHookPluginGUI";
                $target = $ilCtrl->getLinkTargetByClass(array('iluihookplugingui', 'ilcduihookgui', 'cddesktopgui', 'ilpersonalsettingsgui'), 'showPassword', '', false, false);
                //ilias.php?cmd=showPassword&cmdClass=ilpersonalsettingsgui&cmdNode=q:rs:rf:7&baseClass=ilUIHookPluginGUI
                return array("mode" => ilUIHookPluginGUI::REPLACE, "html" => $target);
            }
        }

        // this redirect adjustement works together with the patch in
        // Services/User/classes/class.ilUserProfileIncompleteRequestTargetAdjustmentCase.php
        if ($a_comp == "Services/Utilities" && $a_part == "redirect") {
            if (is_int(strpos($a_par["html"], "cmd=showPersonalData")) &&
                is_int(strpos($a_par["html"], "baseClass=ilDashboardGUI"))) {
                $_GET["baseClass"] = "ilUIHookPluginGUI";
                $target = $ilCtrl->getLinkTargetByClass(array('iluihookplugingui', 'ilcduihookgui', 'cddesktopgui', 'ilpersonalprofilegui'), 'showPersonalData', '', false, false);
                //ilias.php?cmd=showPassword&cmdClass=ilpersonalsettingsgui&cmdNode=q:rs:rf:7&baseClass=ilUIHookPluginGUI
                return array("mode" => ilUIHookPluginGUI::REPLACE, "html" => $target);
            }
        }


        return array("mode" => ilUIHookPluginGUI::KEEP, "html" => "");
    }

    /**
     * Get custom entry
     * @param
     * @return
     */
    protected function getCustomEntry($title, $link)
    {
        global $DIC;
        $ui = $DIC->ui();
        return $ui->factory()->legacy('<li><a href="' . $link . '" class="dropdown-toggle">' . $title . '</a></li>');
    }

    /**
     * Replace main menu list entries
     *
     * @param
     * @return
     */
    public function replaceMainMenuListEntries($a_par)
    {
        global $DIC;

        $lng = $DIC->language();
        $setting = $DIC->settings();
        $access = $DIC->access();
        $tree = $DIC->repositoryTree();

        $top_items = (new ilMMItemRepository())->getStackedTopItemsForPresentation();
        $tpl = new ilTemplate("tpl.main_menu_legacy.html", true, true, 'Services/MainMenu');
        /**
         * @var $top_item \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem|\ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem
         */
        $components = [];

        // cd desktop
        $components[] = $this->getCustomEntry(
            $lng->txt("personal_desktop"),
            "ilias.php?baseClass=ilUIHookPluginGUI&cmd=setCmdClass&cmdClass=ilcduihookgui&forwardTo=cdDesktopGUI"
        );

        // material
        if ($setting->get("cd_mat_ref_id") > 0 && $access->checkAccess("read", "", $setting->get("cd_mat_ref_id"))) {
            $components[] = $this->getCustomEntry(
                $this->getPluginObject()->txt("material"),
                ilLink::_getStaticLink($setting->get("cd_mat_ref_id"), 'cat', true)
            );
        }

        // cd repository
        $user_admin_centers = ilCDPermWrapper::getAdminCenters();
        if (($access->checkAccess("visible", "", ROOT_FOLDER_ID)
            && is_array($user_admin_centers) && count($user_admin_centers) > 0) ||
            ilCDPermWrapper::isAdmin()) {
            $nd = $tree->getNodeData(ROOT_FOLDER_ID);
            $title = $nd["title"];
            if ($title == "ILIAS") {
                $title = $lng->txt("repository");
            }
            $components[] = $this->getCustomEntry(
                $title,
                ilLink::_getStaticLink(1, 'root', true)
            );
        }

        // cd companies
        if (is_array($user_admin_centers) && count($user_admin_centers) > 0) {
            $components[] = $this->getCustomEntry(
                $this->getPluginObject()->txt("companies"),
                "ilias.php?baseClass=ilUIHookPluginGUI&cmd=setCmdClass&cmdClass=ilcduihookgui&forwardTo=cdCompanyGUI"
            );
        } else {
            // participants list for kunde-per roles
            if (ilCDPermWrapper::isPerRole()) {
                $components[] = $this->getCustomEntry(
                    $this->getPluginObject()->txt("participants"),
                    "ilias.php?baseClass=ilUIHookPluginGUI&cmd=setCmdClass&cmdClass=ilcduihookgui&forwardTo=cdCompanyGUI"
                );
            }
        }

        // trainer
        $user_admin_centers = ilCDPermWrapper::getAdminCenters();
        if (is_array($user_admin_centers) && count($user_admin_centers) > 0) {
            $components[] = $this->getCustomEntry(
                $this->getPluginObject()->txt("trainers"),
                "ilias.php?baseClass=ilUIHookPluginGUI&cmd=setCmdClass&cmdClass=ilcduihookgui&forwardTo=ilCDTrainerGUI"
            );
        }

        // administration
        reset($top_items);
        foreach ($top_items as $top_item) {
            if ($top_item->getTitle() == $lng->txt("administration")) {
                $components[] = $top_item->getTypeInformation()->getRenderer()->getComponentForItem($top_item);
            }
        }

        $tpl->setVariable("ENTRIES", $DIC->ui()->renderer()->render($components));

        return array("mode" => ilUIHookPluginGUI::REPLACE, "html" => $tpl->get());
    }

    public function gotoHook() : void
    {
        global $DIC;

        $ctrl = $DIC->ctrl();

        $target = trim($_GET["target"] ?? "");
        if ($target === "needsanalysis") {
            if (cdUtil::isDAF()) {
                $ctrl->redirectByClass(["ilUIHookPluginGUI", "ilCDUIHookGUI", "cdDesktopGUI", "cdNeedsAnalysisDAFGUI"]);
            } else {
                $ctrl->redirectByClass(["ilUIHookPluginGUI", "ilCDUIHookGUI", "cdDesktopGUI", "cdNeedsAnalysisGUI"]);
            }
        }
        if ($target === "selfeval") {
            $ctrl->redirectByClass(["ilUIHookPluginGUI", "ilCDUIHookGUI", "cdDesktopGUI", "ilSkillSelfEvaluationGUI"]);
        }
        if ($target === "entrylevel") {
            $ctrl->redirectByClass(["ilUIHookPluginGUI", "ilCDUIHookGUI", "cdDesktopGUI", "cdEntryLevelTestGUI"]);
        }
        if ($target === "studio") {
            $ctrl->redirectByClass(["ilUIHookPluginGUI", "ilCDUIHookGUI", "cdDesktopGUI"], "showLearningStudio");
        }
    }
}
