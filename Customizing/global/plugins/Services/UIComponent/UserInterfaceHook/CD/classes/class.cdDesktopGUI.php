<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * CD Desktop
 *
 * @author Alex Killin <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_Calls cdDesktopGUI: cdNeedsAnalysisGUI, ilSkillSelfEvaluationGUI
 * @ilCtrl_Calls cdDesktopGUI: cdEntryLevelTestGUI, ilPersonalProfileGUI, cdAddonGUI, ilPersonalSettingsGUI
 * @ilCtrl_Calls cdDesktopGUI: ilCDTrainerGUI, cdNeedsAnalysisDAFGUI
 */
class cdDesktopGUI
{
    /**
     * Constructor
     *
     * @param
     * @return
     */
    public function __construct($a_pl)
    {
        global $ilCtrl;

        $this->pl = $a_pl;
        
        $this->readCourses();
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $tpl, $lng, $ilUser;

        $lng->loadLanguageModule("dash");
        //$tpl->setTitle($lng->txt("dash_dashboard"));
        //$tpl->setTitleIcon(ilUtil::getImagePath("icon_pd.svg"));

        if (in_array($ilUser->getId(), [ANONYMOUS_USER_ID, 0])) {
            return;
        }

        $cmd = $ilCtrl->getCmd();
        $next_class = $ilCtrl->getNextClass();

        switch ($next_class) {
            case "cdneedsanalysisgui":
            case "cdneedsanalysisdafgui":
                $this->setTabs("needs_analysis");
                $this->pl->includeClass("class.cdNeedsAnalysisFactory.php");
                $needs_gui = cdNeedsAnalysisFactory::getGUIInstance($this->pl);
                $ilCtrl->forwardCommand($needs_gui);
                $tpl->printToStdOut();
                break;

            case "ilskillselfevaluationgui":
                $this->setTabs("self_evaluation");
                $se_gui = new ilSkillSelfEvaluationGUI();
                $ilCtrl->forwardCommand($se_gui);
                $tpl->printToStdOut();
                break;

            case "cdentryleveltestgui":
                $this->setTabs("entry_level_test");
                $this->pl->includeClass("class.cdEntryLevelTestGUI.php");
                $needs_gui = new cdEntryLevelTestGUI($this->pl);
                $ilCtrl->forwardCommand($needs_gui);
                $tpl->printToStdOut();
                break;

            case "ilpersonalprofilegui":
                $this->setTabs("profile");
                $this->setSubTabs("personal_data");
                $profile_gui = new ilPersonalProfileGUI();
                $ilCtrl->forwardCommand($profile_gui);
                break;

            case "ilpersonalsettingsgui":
                $this->setTabs("profile");
                if ($_GET["cmd"] == "showGeneralSettings") {
                    $this->setSubTabs("general_settings");
                } else {
                    $this->setSubTabs("password");
                }
                $profile_gui = new ilPersonalSettingsGUI();
                $ilCtrl->forwardCommand($profile_gui);
                break;

            case "cdaddongui":
                $this->setTabs("addon");
                $this->pl->includeClass("class.cdAddonGUI.php");
                $addon_gui = new cdAddonGUI($this->pl);
                $ilCtrl->forwardCommand($addon_gui);
                $tpl->printToStdOut();
                break;

            case "ilcdtrainergui":
                $this->setTabs("profile");
                $this->setSubTabs("personal_data");
                $cd_gui = new ilCDTrainerGUI($this->pl);
                $ilCtrl->forwardCommand($cd_gui);
//				$tpl->printToStdOut();
                break;

            default:
                if ($cmd == "") {
                    if (count($this->courses) > 0) {
                        $cmd = "listCourses";
                    } else {
                        if (ilCDTrainer::isTrainer($ilUser->getId())) {
                            $ilCtrl->redirectByClass(array("cddesktopgui", "ilcdtrainergui"));
                        } else {
                            $ilCtrl->setCmdClass("ilpersonalprofilegui");
                        }
                        return $this->executeCommand();
                    }
                }
                $this->$cmd();
                $tpl->printToStdOut();
                break;
        }
    }

    /**
     * Set tabs
     *
     * @param
     * @return
     */
    public function setTabs($a_active)
    {
        global $ilTabs, $ilCtrl, $lng, $ilUser, $ilSetting;
        return;
        if (count($this->courses) > 0) {
            $ilTabs->addTab(
                "courses",
                $lng->txt("objs_crs"),
                $ilCtrl->getLinkTarget($this, "listCourses")
            );
        }

        if (ilCDTrainer::isTrainer($ilUser->getId())) {
            $ilTabs->addTab(
                "profile",
                $lng->txt("personal_profile"),
                $ilCtrl->getLinkTargetByClass(array("cddesktopgui", "ilcdtrainergui"), "")
            );
        } else {
            $ilTabs->addTab(
                "profile",
                $lng->txt("personal_profile"),
                $ilCtrl->getLinkTargetByClass("ilpersonalprofilegui", "")
            );
        }

        $ilTabs->addTab(
            "needs_analysis",
            $this->pl->txt("needs_analysis_reiter"),
            $ilCtrl->getLinkTargetByClass("cdneedsanalysisgui", "")
        );

        $ilTabs->addTab(
            "self_evaluation",
            $this->pl->txt("self_evaluation"),
            $ilCtrl->getLinkTargetByClass("ilskillselfevaluationgui", "")
        );

        $ilTabs->addTab(
            "entry_level_test",
            $this->pl->txt("entry_level_test"),
            $ilCtrl->getLinkTargetByClass("cdentryleveltestgui", "")
        );
        
        if ($ilUser->getPasswordAddon() != "") {
            $ilTabs->addTab(
                "addon",
                $this->pl->txt("tab_addon"),
                $ilCtrl->getLinkTargetByClass(array("cddesktopgui", "cdaddongui"), "")
            );
        }

        if ($ilSetting->get("cd_learn_studio") != "") {
            $ilTabs->addTab(
                "learning_studio",
                $this->pl->txt("learning_studio"),
                $ilCtrl->getLinkTarget($this, "showLearningStudio")
            );
        }

        $ilTabs->activateTab($a_active);
    }

    /**
     * Set sub tabs
     *
     * @param
     * @return
     */
    public function setSubTabs($a_active)
    {
        global $ilTabs, $lng, $ilCtrl, $ilUser;

        // personal data
        if (ilCDTrainer::isTrainer($ilUser->getId())) {
            $ilTabs->addSubTab(
                "personal_data",
                $lng->txt("personal_data"),
                $ilCtrl->getLinkTargetByClass(array("cddesktopgui", "ilcdtrainergui"), "")
            );
        } else {
            $ilTabs->addSubTab(
                "personal_data",
                $lng->txt("personal_data"),
                $ilCtrl->getLinkTargetByClass("ilpersonalprofilegui", "showPersonalData")
            );
        }

        // password
        if (ilAuthUtils::isPasswordModificationEnabled($ilUser->getAuthMode(true))) {
            $ilTabs->addSubTab(
                "password",
                $lng->txt("password"),
                $ilCtrl->getLinkTargetByClass("ilpersonalsettingsgui", "showPassword")
            );
        }

        // general settings
        $ilTabs->addSubTab(
            "general_settings",
            $lng->txt("general_settings"),
            $ilCtrl->getLinkTargetByClass("ilpersonalsettingsgui", "showGeneralSettings")
        );

        $ilTabs->activateSubTab($a_active);
    }


    ////
    //// Needs Analysis
    ////

    /**
     * Show needs analysis
     */
    public function showNeedsAnalysis()
    {
        global $tpl;

        $this->setTabs("needs_analysis");
        $tpl->setContent("Needs Analysis");
    }


    ////
    //// Self Evaluation
    ////

    /**
     * Show self evaluation
     */
    public function showSelfEvaluation()
    {
        global $tpl;

        $this->setTabs("self_evaluation");
        $tpl->setContent("Self Evaluation");
    }

    ////
    //// Entry Level Test
    ////

    /**
     * Show entry level test
     */
    public function showEntryLevelTest()
    {
        global $tpl;

        $this->setTabs("entry_level_test");
        $tpl->setContent("Entry Level Test");
    }

    ////
    //// Courses
    ////
    
    /**
     * Read courses
     *
     * @param
     * @return
     */
    public function readCourses()
    {
        global $ilUser, $ilObjDataCache, $tree, $ilAccess;
        
        $items = ilParticipants::_getMembershipByType($ilUser->getId(), 'crs');

        $references = array();
        foreach ($items as $key => $obj_id) {
            $item_references = ilObject::_getAllReferences($obj_id);
            if (is_array($item_references) && count($item_references)) {
                foreach ($item_references as $ref_id) {
                    $title = $ilObjDataCache->lookupTitle($obj_id);
                    $type = $ilObjDataCache->lookupType($obj_id);
                    
                    if ($ilAccess->checkAccess("visible", "", $ref_id)) {
                        $references[$title . $ref_id] =
                            array('ref_id' => $ref_id,
                                  'obj_id' => $obj_id,
                                  'type' => $type,
                                  'title' => $title,
                                  'description' => $ilObjDataCache->lookupDescription($obj_id),
                                  'parent_ref' => $tree->getParentId($ref_id));
                    }
                }
            }
        }
        ksort($references);

        $this->courses = $references;
    }

    /**
     * List courses
     */
    public function listCourses()
    {
        global $tpl;
        
        $this->setTabs("courses");
        
        // course list
        $this->pl->includeClass("class.cdCoursesTableGUI.php");
        $t = new cdCoursesTableGUI($this, "listCourses", $this->pl, $this->courses);
        
        // evaluation notifications
        $this->pl->includeClass("class.cdEvaluationNotificationsTableGUI.php");
        $t2 = new cdEvaluationNotificationsTableGUI($this, "listCourses", $this->pl, $this->courses);
        $this->pl->includeClass("class.cdParticipantEvaluation.php");
        $data = cdParticipantEvaluation::getEvalNotificationsForCourses($this->courses);
        //var_dump($data);
        if (count($data) > 0) {
            $t2->setData($data);
            $tpl->setContent($t->getHTML() . "<br />" . $t2->getHTML());
            return;
        }
        
        $tpl->setContent($t->getHTML());
    }

    /**
     * Show learning studio
     *
     * @param
     * @return
     */
    protected function showLearningStudio()
    {
        global $ilSetting, $tpl, $lng;

        $tpl->setTitle($this->pl->txt("learning_studio"));
        $this->setTabs("learning_studio");

        if ($ilSetting->get("cd_learn_studio") != "") {
            $ls_tpl = $this->pl->getTemplate("tpl.learn_studio.html");
            $ls_tpl->setVariable("SRC", $ilSetting->get("cd_learn_studio"));
            $ls_tpl->setVariable("TXT", $this->pl->txt("open_learning_studio"));
            $tpl->setContent($ls_tpl->get());
        }
    }
}
