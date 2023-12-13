<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Needs analysis
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class cdNeedsAnalysisGUI
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
        $this->pl->includeClass("class.cdNeedsAnalysis.php");
        
        $ilCtrl->saveParameter($this, array("na_id"));

        $this->readNeedsAnalysis();
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $ilUser, $tpl;

        $cmd = $ilCtrl->getCmd();

        $tpl->setTitle($this->pl->txt("needs_analysis_reiter"));

        // determine default command
        $a = cdNeedsAnalysis::getNeedsAnalysesOfUser($ilUser->getId());
        if ($cmd == "") {
            if (count($a) == 0) {
                $cmd = "addNeedsAnalysis";
            } else {
                $cmd = "listNeedsAnalyses";
            }
        }

        $this->$cmd();
    }

    /**
     * Read needs analysis
     *
     * @param
     * @return
     */
    public function readNeedsAnalysis()
    {
        $this->pl->includeClass("class.cdNeedsAnalysisFactory.php");
        $this->needs_analysis = cdNeedsAnalysisFactory::getInstance($this->pl, (int) $_GET["na_id"]);
    }

    /**
     * List all needs analysis
     *
     * @param
     * @return
     */
    public function listNeedsAnalyses()
    {
        global $tpl, $ilToolbar, $ilCtrl, $lng;

        $ilToolbar->addButton(
            $this->pl->txt("add_needs_analysis"),
            $ilCtrl->getLinkTarget($this, "addNeedsAnalysis")
        );

        $this->pl->includeClass("class.cdNeedsAnalysisTableGUI.php");
        $table = new cdNeedsAnalysisTableGUI($this, "listNeedsAnalyses", $this->pl);

        $tpl->setContent($table->getHTML());
    }

    /**
     * Add needs analysis
     */
    public function addNeedsAnalysis()
    {
        global $tpl, $ilCtrl;

        $ilCtrl->setParameter($this, "step", $_GET["step"]);
        $this->initNeedsAnalysisForm("create");
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Edit needs analysis
     */
    public function editNeedsAnalysis()
    {
        global $tpl, $ilCtrl;

        $ilCtrl->setParameter($this, "step", $_GET["step"]);
        $this->initNeedsAnalysisForm("edit");
        $this->getNeedsAnalysisValues();
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Init needs analysis form.
     *
     * @param        int        $a_mode        Edit Mode
     */
    public function initNeedsAnalysisForm($a_mode = "edit")
    {
        global $lng, $ilCtrl, $tpl;

        $step = (int) $_GET["step"];

        $this->form = new ilPropertyFormGUI();

        $lng->loadLanguageModule("meta");

        $this->pl->includeClass("class.cdLang.php");

        switch ($step) {
            case 0:

                $this->form->setTitle($this->pl->txt("needs_analysis"));

                // course lang
                $options = array();
                foreach (cdLang::getLanguages() as $k) {
                    $options[$k] = $lng->txt("meta_l_" . $k);
                }
                asort($options);
                $si = new ilSelectInputGUI($this->pl->txt("course_lang_q"), "course_lang");
                $si->setOptions($options);
                $this->form->addItem($si);

                // other languages (selected)
                $cg = new ilCheckboxGroupInputGUI(
                    $this->pl->txt("other_languages_q"),
                    "other_lang_sel"
                );
                foreach (cdLang::getLanguages() as $k) {
                    $op = new ilCheckboxOption($lng->txt("meta_l_" . $k), $k);
                    $cg->addOption($op);
                }
                $this->form->addItem($cg);

                // other languages free
                $ta = new ilTextAreaInputGUI(
                    $this->pl->txt("other_lang_free_q"),
                    "other_lang_free"
                );
                //$ta->setCols();
                //$ta->setRows();
                //$ta->setInfo($lng->txt(""));
                $this->form->addItem($ta);

                // last course
                $options = array("" => $this->pl->txt("option_never"));
                for ($i = 2018; $i >= 1950; $i--) {
                    $options[$i] = $i;
                }
                $si = new ilSelectInputGUI($this->pl->txt("last_course_q"), "last_course");
                $si->setOptions($options);
                $this->form->addItem($si);

                // lang_level
                $options = array("" => $this->pl->txt("option_none"));
                foreach (cdNeedsAnalysis::$lang_levels as $l) {
                    $options[$l] = $l;
                }
                $si = new ilSelectInputGUI($this->pl->txt("lang_level_q"), "lang_level");
                $si->setOptions($options);
                $this->form->addItem($si);
                break;

            case 1:

//				$tpl->addJavascript("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CD/js/lib/jquery-1.8.1.min.js");
                $tpl->addJavascript("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CD/js/cd_ui.js");
                $tpl->addOnLoadCode("cdUI.setNaText('" . $this->pl->txt("na_max_elements") . "');");
                
                $this->form->setTitle($this->pl->txt("which_situations_q"));

                // talk and understanding
                $sh = new ilFormSectionHeaderGUI();
                $sh->setTitle($this->pl->txt("talk_and_understand"));
                $this->form->addItem($sh);

                // talk and understand
                foreach (cdNeedsAnalysis::$talk_understand as $k => $l) {
                    //
                    $cb = new ilCheckboxInputGUI($this->pl->txt($l), "talk_understand_" . $k);
                    $cb->setValue(1);
                    $this->form->addItem($cb);
                    
                    /*
                    $radg = new ilRadioGroupInputGUI($this->pl->txt($l), "talk_understand_".$k);
                    $op1 = new ilRadioOption($this->pl->txt("freq_1"), 1);
                    $radg->addOption($op1);
                    $op0 = new ilRadioOption($this->pl->txt("freq_0"), 0);
                    $radg->addOption($op0);
                    #$op3 = new ilRadioOption($this->pl->txt("freq_3"), 3);
                    #$radg->addOption($op3);
                    #$op2 = new ilRadioOption($this->pl->txt("freq_2"), 2);
                    #$radg->addOption($op2);
                    $this->form->addItem($radg);*/
                }
                break;

            case 2:

//				$tpl->addJavascript("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CD/js/lib/jquery-1.8.1.min.js");
                $tpl->addJavascript("./Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/CD/js/cd_ui.js");
                $tpl->addOnLoadCode("cdUI.setNaText('" . $this->pl->txt("na_max_elements") . "');");

                $this->form->setTitle($this->pl->txt("which_situations_q"));

                // write and read
                $sh = new ilFormSectionHeaderGUI();
                $sh->setTitle($this->pl->txt("write_and_read"));
                $this->form->addItem($sh);

                // write and read
                foreach (cdNeedsAnalysis::$write_read as $k => $l) {
                    $cb = new ilCheckboxInputGUI($this->pl->txt($l), "write_read_" . $k);
                    $cb->setValue(1);
                    $this->form->addItem($cb);

                    
                    /*$radg = new ilRadioGroupInputGUI($this->pl->txt($l), "write_read_".$k);
                    $op1 = new ilRadioOption($this->pl->txt("freq_1"), 1);
                    $radg->addOption($op1);
                    $op0 = new ilRadioOption($this->pl->txt("freq_0"), 0);
                    $radg->addOption($op0);*/
                    #$op3 = new ilRadioOption($this->pl->txt("freq_3"), 3);
                    #$radg->addOption($op3);
                    #$op2 = new ilRadioOption($this->pl->txt("freq_2"), 2);
                    #$radg->addOption($op2);
                    //$this->form->addItem($radg);
                }
                break;

            case 3:

                $this->form->setTitle($this->pl->txt("technical_language_q"));
                
                // technical language
                $options = array("" => $this->pl->txt("option_none"));
                foreach (cdNeedsAnalysis::$technical_lang as $k => $v) {
                    $options[$k] = $this->pl->txt($v);
                }
                $si = new ilSelectInputGUI($this->pl->txt("technical_language"), "tech_lang_sel");
                $si->setOptions($options);
                $this->form->addItem($si);

                // technical language other
                $ti = new ilTextInputGUI($this->pl->txt("other_technical_language"), "tech_lang_free");
                $ti->setMaxLength(100);
                $this->form->addItem($ti);
                break;

        }

        ilUtil::sendInfo($this->pl->txt("step") . " " . ($step + 1) . " / 4");

        // save and cancel commands
        if ($a_mode == "create") {
            $this->form->addCommandButton("saveNeedsAnalysis", $this->pl->txt("next_step") . " >");
        } else {
            if ($step > 0) {
                $this->form->addCommandButton(
                    "updateNeedsAnalysisBack",
                    "< " . $this->pl->txt("previous_step")
                );
            }
            if ($step < 3) {
                $this->form->addCommandButton(
                    "updateNeedsAnalysis",
                    $this->pl->txt("next_step") . " >"
                );
            } else {
                $this->form->addCommandButton(
                    "updateNeedsAnalysis",
                    $this->pl->txt("finish_needs_analysis")
                );
            }
            //$this->form->addCommandButton("listNeedsAnalyses", $lng->txt("cancel"));
        }

        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    /**
     * Get current values for needs analysis from
     */
    public function getNeedsAnalysisValues()
    {
        $values = array();

        $values["course_lang"] = $this->needs_analysis->getCourseLanguage();
        $sel = unserialize($this->needs_analysis->getOtherLangSel());
        $values["other_lang_sel"] = $sel;
        $values["other_lang_free"] = $this->needs_analysis->getOtherLangFree();
        $values["last_course"] = $this->needs_analysis->getLastCourse();
        $values["lang_level"] = $this->needs_analysis->getLangLevel();
        $sel = unserialize($this->needs_analysis->getTalkUnderstanding());
        foreach (cdNeedsAnalysis::$talk_understand as $k => $l) {
            $values["talk_understand_" . $k] = $sel[$k];
        }
        $sel = unserialize($this->needs_analysis->getWriteRead());
        foreach (cdNeedsAnalysis::$write_read as $k => $l) {
            $values["write_read_" . $k] = $sel[$k];
        }
        $values["tech_lang_sel"] = $this->needs_analysis->getTechnicalLanguage();
        $values["tech_lang_free"] = $this->needs_analysis->getTechnicalLanguageFree();
        
        $this->form->setValuesByArray($values);
    }

    /**
     * Save needs analysis form
     */
    public function saveNeedsAnalysis()
    {
        global $tpl, $lng, $ilCtrl, $ilUser;

        $this->initNeedsAnalysisForm("create");
        if ($this->form->checkInput()) {
            $this->pl->includeClass("class.cdNeedsAnalysisFactory.php");
            $needs_analysis = cdNeedsAnalysisFactory::getInstance($this->pl);
            $needs_analysis->setUserId($ilUser->getId());
            $needs_analysis->setCourseLanguage($_POST["course_lang"]);
            $sel = array();
            if (is_array($_POST["other_lang_sel"])) {
                foreach ($_POST["other_lang_sel"] as $l) {
                    $sel[] = $l;
                }
            }
            $sel = serialize($sel);

            $needs_analysis->setOtherLangSel($sel);
            $needs_analysis->setOtherLangFree($_POST["other_lang_free"]);
            $needs_analysis->setLastCourse($_POST["last_course"]);
            $needs_analysis->setLangLevel($_POST["lang_level"]);

            /*
            $sel = array();
            foreach (cdNeedsAnalysis::$talk_understand as $k => $l)
            {
                $sel[$k] = (int) $_POST["talk_understand_".$k];
            }
            $sel = serialize($sel);
            $needs_analysis->setTalkUnderstanding($sel);

            $sel = array();
            foreach (cdNeedsAnalysis::$write_read as $k => $l)
            {
                $sel[$k] = (int) $_POST["write_read_".$k];
            }
            $sel = serialize($sel);
            $needs_analysis->setWriteRead($sel);

            $needs_analysis->setTechnicalLanguage($_POST["tech_lang_sel"]);
            $needs_analysis->setTechnicalLanguageFree($_POST["tech_lang_free"]);
            */

            $needs_analysis->create();


            // perform save

            //			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->setParameter($this, "step", 1);
            $ilCtrl->setParameter($this, "na_id", $needs_analysis->getId());
            //$ilCtrl->redirect($this, "listNeedsAnalyses");
            $ilCtrl->redirect($this, "editNeedsAnalysis");
        }

        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHtml());
    }

    /**
     * Update needs analysis
     */
    public function updateNeedsAnalysisBack()
    {
        $this->updateNeedsAnalysis(true);
    }

    /**
     * Update needs analysis
     */
    public function updateNeedsAnalysis($a_back = false)
    {
        global $lng, $ilCtrl, $tpl;

        $step = (int) $_GET["step"];
        
        $this->initNeedsAnalysisForm("edit");
        if ($this->form->checkInput()) {
            $needs_analysis = $this->needs_analysis;

            switch ($step) {
                case 0:
                    $needs_analysis->setCourseLanguage($_POST["course_lang"]);
                    $sel = array();
                    if (is_array($_POST["other_lang_sel"])) {
                        foreach ($_POST["other_lang_sel"] as $l) {
                            $sel[] = $l;
                        }
                    }
                    $sel = serialize($sel);

                    $needs_analysis->setOtherLangSel($sel);
                    $needs_analysis->setOtherLangFree($_POST["other_lang_free"]);
                    $needs_analysis->setLastCourse($_POST["last_course"]);
                    $needs_analysis->setLangLevel($_POST["lang_level"]);
                    break;

                case 1:
                    $sel = array();
                    foreach (cdNeedsAnalysis::$talk_understand as $k => $l) {
                        $sel[$k] = (int) $_POST["talk_understand_" . $k];
                    }
                    $sel = serialize($sel);
                    $needs_analysis->setTalkUnderstanding($sel);
                    break;

                case 2:

                    $sel = array();
                    foreach (cdNeedsAnalysis::$write_read as $k => $l) {
                        $sel[$k] = (int) $_POST["write_read_" . $k];
                    }
                    $sel = serialize($sel);
                    $needs_analysis->setWriteRead($sel);
                    break;

                case 3:
                    $needs_analysis->setTechnicalLanguage($_POST["tech_lang_sel"]);
                    $needs_analysis->setTechnicalLanguageFree($_POST["tech_lang_free"]);
                    break;
            }

            // perform update
            $needs_analysis->update();
            if ($a_back) {
                $next_step = $step - 1;
            } else {
                $next_step = $step + 1;
            }

            if ($next_step < 4) {
                //ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
                $ilCtrl->setParameter($this, "step", $next_step);
                $ilCtrl->redirect($this, "editNeedsAnalysis");
            } else {
                ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
                $ilCtrl->redirect($this, "listNeedsAnalyses");
            }
        }

        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHtml());
    }

    /**
     * Confirm needs analysis deletion
     */
    public function confirmNeedsAnalysisDeletion()
    {
        global $ilCtrl, $tpl, $lng;

        $lng->loadLanguageModule("meta");

        if (!is_array($_POST["needs_analysis_id"]) || count($_POST["needs_analysis_id"]) == 0) {
            ilUtil::sendInfo($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listNeedsAnalyses");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($this->pl->txt("sure_delete_needs_analysis"));
            $cgui->setCancel($lng->txt("cancel"), "listNeedsAnalyses");
            $cgui->setConfirm($lng->txt("delete"), "deleteNeedsAnalysis");

            foreach ($_POST["needs_analysis_id"] as $i) {
                $cgui->addItem(
                    "needs_analysis_id[]",
                    $i,
                    $lng->txt("meta_l_" . cdNeedsAnalysis::lookupLanguage($i)) . " (" .
                    cdNeedsAnalysis::lookupCreated($i) . ")"
                );
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete needs analysis
     *
     * @param
     * @return
     */
    public function deleteNeedsAnalysis()
    {
        global $ilCtrl, $lng;

        if (is_array($_POST["needs_analysis_id"])) {
            foreach ($_POST["needs_analysis_id"] as $i) {
                $this->pl->includeClass("class.cdNeedsAnalysisFactory.php");
                $needs_analysis = cdNeedsAnalysisFactory::getInstance($this->pl, $i);
                $needs_analysis->delete();
            }
        }
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "listNeedsAnalyses");
    }


    ////
    //// Presentation view
    ////

    /**
     * Get presentation view
     *
     * @param
     * @return
     */
    public function getPresentationView($a_user_id)
    {
        $nas = cdNeedsAnalysis::getNeedsAnalysesOfUser($a_user_id);

        $html = "";
        foreach ($nas as $na) {
            $this->setNeedsAnalysisPresentationForm($na);
            $html .= $this->form->getHTML() . "<br /><br />";
        }

        return $html;
    }

    /**
     * Init needs analysis form.
     */
    public function setNeedsAnalysisPresentationForm($na)
    {
        global $lng, $ilCtrl;

        $this->form = new ilPropertyFormGUI();

        ilDatePresentation::setUseRelativeDates(false);
        $dates = ", " .
            $lng->txt("created") . ": " .
            ilDatePresentation::formatDate(
                new ilDateTime($na["ts"], IL_CAL_DATETIME)
            );
        if ($na["ts"] != $na["last_update"]) {
            $dates .= ", " . $lng->txt("last_update") . ": " .
            ilDatePresentation::formatDate(
                new ilDateTime($na["last_update"], IL_CAL_DATETIME)
            );
        }
        ilDatePresentation::setUseRelativeDates(true);

        $this->form->setTitle($this->pl->txt("needs_analysis") . $dates);

        $lng->loadLanguageModule("meta");

        $fields = array(
            "course_lang" => "course_lang",
            "other_lang_sel" => "other_languages",
            "last_course" => "last_course",
            "lang_level" => "lang_level",
            );

        // leading fields
        foreach ($fields as $f => $l) {
            $ne = new ilNonEditableValueGUI($this->pl->txt($l), $f);
            $v = "";
            switch ($f) {
                case "course_lang":
                    $v = $lng->txt("meta_l_" . $na["course_lang"]);
                    break;

                case "other_lang_sel":
                    $sel = unserialize($na["olang_sel"]);
                    if (is_array($sel)) {
                        $sep = "";
                        foreach ($sel as $s) {
                            $v .= $sep . $lng->txt("meta_l_" . $s);
                            $sep = ", ";
                        }
                    }
                    break;

                case "last_course":
                    if (!(int) $na["last_course"]) {
                        $v = $this->pl->txt("option_never");
                    } else {
                        $v = $na["last_course"];
                    }
                    break;

                case "lang_level":
                    if ($na["lang_level"] == "") {
                        $v = $this->pl->txt("option_none");
                    } else {
                        $v = $na["lang_level"];
                    }
                    break;
            }
            $ne->setValue($v);
            $this->form->addItem($ne);
        }

        // situations
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->pl->txt("which_situations"));
        $this->form->addItem($sh);

        // talk and understanding
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->pl->txt("talk_and_understand"));
        $this->form->addItem($sh);

        // talk and understand
        //var_dump($na);
        $ta = unserialize($na["talk_understanding"]);
        foreach (cdNeedsAnalysis::$talk_understand as $k => $l) {
            $ne = new ilNonEditableValueGUI(
                $this->pl->txt($l),
                "talk_understand_" . $k
            );
            if ($ta[$k] === 0) {
                $caption = $this->pl->txt("option_never");
            } else {
                $caption = $this->pl->txt("freq_" . $ta[$k]);
            }
            $ne->setValue($caption);
            $this->form->addItem($ne);
        }

        // write and read
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->pl->txt("write_and_read"));
        $this->form->addItem($sh);

        // write and read
        $wr = unserialize($na["write_read"]);
        foreach (cdNeedsAnalysis::$write_read as $k => $l) {
            $ne = new ilNonEditableValueGUI(
                $this->pl->txt($l),
                "talk_understand_" . $k
            );
            if ($wr[$k] === 0) {
                $caption = $this->pl->txt("option_never");
            } else {
                $caption = $this->pl->txt("freq_" . $wr[$k]);
            }
            $ne->setValue($caption);
            $this->form->addItem($ne);
        }

        // technical language
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->pl->txt("technical_language"));
        $this->form->addItem($sh);

        $fields = array(
            "tech_lang_sel" => "technical_language",
            "tech_lang_free" => "other_technical_language"
            );

        // leading fields
        $tl = cdNeedsAnalysis::$technical_lang;
        foreach ($fields as $f => $l) {
            $ne = new ilNonEditableValueGUI($this->pl->txt($l), $f);
            $v = "";
            switch ($f) {
                case "tech_lang_sel":
                    if (!(int) $na["tech_lang_sel"]) {
                        $v = $this->pl->txt("option_none");
                    } else {
                        $v = $this->pl->txt($tl[$na["tech_lang_sel"]]);
                    }
                    break;

                case "tech_lang_free":
                    $v = $na["tech_lang_free"];
                    break;
            }
            $ne->setValue($v);
            $this->form->addItem($ne);
        }
    }
}
