<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Needs analysis for DAF
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class cdNeedsAnalysisDAFGUI extends cdNeedsAnalysisGUI
{
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
                foreach (array("de") as $k) {
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
                    if ($k != "de") {
                        $op = new ilCheckboxOption($lng->txt("meta_l_" . $k), $k);
                        $cg->addOption($op);
                    }
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

                $this->form->setTitle("&nbsp;");

                // talk and understand
                foreach (cdNeedsAnalysisDAF::getQuestions($this->pl) as $k => $question) {
                    //
                    $ta = new ilTextAreaInputGUI($question, "daf_answer_" . $k);
                    $ta->setRows(5);
                    $ta->setCols(100);
                    $this->form->addItem($ta);
                }
                break;
        }

        ilUtil::sendInfo($this->pl->txt("step") . " " . ($step + 1) . " / 2");

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
            if ($step < 1) {
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

        // talk and understand
        foreach (cdNeedsAnalysisDAF::getQuestions($this->pl) as $k => $question) {
            $values["daf_answer_" . $k] = $this->needs_analysis->getDAFAnswer($k);
        }


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
                    // talk and understand
                    foreach (cdNeedsAnalysisDAF::getQuestions($this->pl) as $k => $question) {
                        $needs_analysis->setDAFAnswer($k, $_POST["daf_answer_" . $k]);
                    }
                    break;

                case 2:
                case 3:
                    break;

            }

            // perform update
            $needs_analysis->update();
            if ($a_back) {
                $next_step = $step - 1;
            } else {
                $next_step = $step + 1;
            }

            if ($next_step < 2) {
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
        $nas = cdNeedsAnalysisDAF::getNeedsAnalysesOfUser($a_user_id);

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

        foreach (cdNeedsAnalysisDAF::getQuestions($this->pl) as $k => $question) {
            $ne = new ilNonEditableValueGUI(
                $question,
                "daf_answer_" . $k,
                true
            );
            $ne->setValue(nl2br($na["daf"][$k]["answer"]));
            $this->form->addItem($ne);
        }
    }
}
