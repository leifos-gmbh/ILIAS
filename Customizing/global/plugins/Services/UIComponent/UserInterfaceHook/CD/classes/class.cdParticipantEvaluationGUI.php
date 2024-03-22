<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Participant evaluation gui class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class cdParticipantEvaluationGUI
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
        $this->pl->includeClass("class.cdParticipantEvaluation.php");
        
        $ilCtrl->saveParameter($this, array("pe_id", "member_id"));
        
        $this->course_ref_id = (int) $_GET["ref_id"];
        $this->course_id = ilObject::_lookupObjId($this->course_ref_id);
        $this->course = new ilObjCourse($this->course_ref_id);

        $this->readParticipantEvaluation();
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $ilUser;

        $cmd = $ilCtrl->getCmd("listParticipantEvaluations");

        $this->$cmd();
    }

    /**
     * Read participant evaluation
     *
     * @param
     * @return
     */
    public function readParticipantEvaluation()
    {
        $this->pe = new cdParticipantEvaluation((int) $_GET["pe_id"]);
        //var_dump($this->needs_analysis);
    }

    /**
     * List all participant evaluations
     *
     * @param
     * @return
     */
    public function listParticipantEvaluations()
    {
        global $tpl, $ilToolbar, $ilCtrl, $lng, $ilTabs;
        
        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $lng->txt("members"),
            $ilCtrl->getLinkTargetByClass("ilobjcoursegui", "members")
        );

        $ilToolbar->addButton(
            $this->pl->txt("add_participant_evaluation"),
            $ilCtrl->getLinkTarget($this, "addParticipantEvaluation")
        );

        $this->pl->includeClass("class.cdParticipantEvaluationTableGUI.php");
        $table = new cdParticipantEvaluationTableGUI(
            $this,
            "listParticipantEvaluations",
            $this->pl,
            $this->course_id
        );

        $tpl->setContent($table->getHTML());
    }

    /**
     * Add participant evaluation
     */
    public function addParticipantEvaluation()
    {
        global $tpl, $ilCtrl;

        $this->initParticipantEvaluationForm("create");
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Edit participant evaluation
     */
    public function editParticipantEvaluation()
    {
        global $tpl, $ilCtrl, $ilTabs;

        $ilTabs->clearTargets();
        $ilTabs->setBackTarget(
            $this->pl->txt("evaluation_list"),
            $ilCtrl->getLinkTarget($this, "listParticipantEvaluations")
        );
        
        $this->initParticipantEvaluationForm("edit");
        $this->getParticipantEvaluationValues();
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Init participant evaluation form.
     *
     * @param        int        $a_mode        Edit Mode
     */
    public function initParticipantEvaluationForm($a_mode = "edit")
    {
        global $lng, $ilCtrl;

        $step = (int) $_GET["step"];

        $this->form = new ilPropertyFormGUI();

        $this->pl->includeClass("class.cdLang.php");

        $this->form->setTitle($this->pl->txt("participant_evaluation"));

        // participant
        $ne = new ilNonEditableValueGUI($this->pl->txt("participant"), "participant");
        $ne->setValue(ilUserUtil::getNamePresentation($_GET["member_id"], false, false, "", true));
        $this->form->addItem($ne);
        
        // trainer
        $ne = new ilNonEditableValueGUI($this->pl->txt("trainer"), "trainer");
        $tut = ilCourseParticipants::_getInstanceByObjId($this->course_id)->getTutors();
        $tstr = "";
        if (is_array($tut)) {
            foreach ($tut as $t) {
                $tstr = "<div>" . ilUserUtil::getNamePresentation($t, false, false, "", true) . "</div>";
            }
        }
        $ne->setValue($tstr);
        $this->form->addItem($ne);
        
        // course
        $ne = new ilNonEditableValueGUI($lng->txt("obj_crs"), "course");
        $ne->setValue(ilObject::_lookupTitle(ilObject::_lookupObjId((int) $_GET["ref_id"])));
        $this->form->addItem($ne);
        
        // course level
        $ne = new ilNonEditableValueGUI($this->pl->txt("course_level"), "course_level");
        $ne->setValue($this->course->getCourseLevel());
        $this->form->addItem($ne);
        
        // after x lessons
        $axl = new ilNumberInputGUI($this->pl->txt("after_x_lessons"), "after_x_lessons");
        $axl->setMaxLength(4);
        $axl->setSize(4);
        $this->form->addItem($axl);

        // starting cef level
        /* PATCH HELLER UNTERTEILUNG A-C AUF NEUE "LANG"DATEI*/
        $this->pl->includeClass("class.cdLang.php");
        $options = array("" => $this->pl->txt("option_none"));
        foreach (cdLang::getLangLevelsExt() as $l) {
            $options[$l] = $l;
        }
        $scef = new ilSelectInputGUI($this->pl->txt("starting_cef"), "starting_cef");
        $scef->setOptions($options);
        $this->form->addItem($scef);
        
        
        // skill evaluation matrix
        $this->pl->includeClass("class.cdEvalSkillInputGUI.php");
        $skills = new cdEvalSkillInputGUI($this->pl->txt("skills"), "skills", $this->pl);
        $this->form->addItem($skills);
        
        // current cef level
        $options = array("" => $this->pl->txt("option_none"));
        foreach (cdLang::getLangLevelsExt() as $l) {
            $options[$l] = $l;
        }
        $ccef = new ilSelectInputGUI($this->pl->txt("current_cef"), "current_cef");
        $ccef->setOptions($options);
        $this->form->addItem($ccef);
        
        // test (interims/final exam)
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->pl->txt("test_i_f_exam"));
        $this->form->addItem($sh);
        
        // test attendance
        $tatt = new ilRadioGroupInputGUI($this->pl->txt("test_attendance"), "test_attendance");
        $tatt->setValue("0");

        // text level
        $tl = new ilTextInputGUI($this->pl->txt("test_level"), "test_level");
        $tl->setMaxLength(100);
        $tl->setSize(50);
        $this->form->addItem($tl);
        
        $op1 = new ilRadioOption($lng->txt("yes"), "1");
        $tatt->addOption($op1);
        
        // test result
        $tr = new ilTextAreaInputGUI($this->pl->txt("result"), "test_result");
        $tr->setCols(80);
        $tr->setRows(6);
        $op1->addSubItem($tr);

        $op2 = new ilRadioOption($lng->txt("no"), "0");
        $tatt->addOption($op2);
        
        $this->form->addItem($tatt);

        // recommendation
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->pl->txt("recommendation"));
        $this->form->addItem($sh);

        // should stay in group
        $stay = new ilRadioGroupInputGUI($this->pl->txt("stay_in_group"), "stay_in_group");
        $stay->setValue("1");

        $op1 = new ilRadioOption($lng->txt("yes"), "1");
        $stay->addOption($op1);
        
        $op2 = new ilRadioOption($lng->txt("no"), "0");
        $stay->addOption($op2);
        
        $options = array("" => $this->pl->txt("option_none"));
        foreach (cdLang::getLangLevelsExt() as $l) {
            $options[$l] = $l;
        }
        $recl = new ilSelectInputGUI($this->pl->txt("recommended_level"), "recommended_level");
        $recl->setOptions($options);
        $op2->addSubItem($recl);
        
        $this->form->addItem($stay);
        
        // save and cancel commands
        if ($a_mode == "create") {
            $this->form->addCommandButton("saveParticipantEvaluation", $lng->txt("save"));
        } else {
            $this->form->addCommandButton("updateParticipantEvaluation", $lng->txt("save"));
        }

        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    /**
     * Get current values for participant evaluation from
     */
    public function getParticipantEvaluationValues()
    {
        $values = array();

        $values["after_x_lessons"] = $this->pe->getAfterXLessons();
        $values["starting_cef"] = $this->pe->getStartingCef();
        $values["current_cef"] = $this->pe->getCurrentCef();
        $values["skills"] = $this->pe->getSkills();
        $values["test_attendance"] = $this->pe->getTestAttendance();
        $values["test_result"] = $this->pe->getTestResult();
        $values["stay_in_group"] = $this->pe->getStayInGroup();
        $values["recommended_level"] = $this->pe->getRecommendedLevel();
        $values["test_level"] = $this->pe->getTestLevel();

        $this->form->setValuesByArray($values);
    }

    /**
     * Save participant evaluation form
     */
    public function saveParticipantEvaluation()
    {
        global $tpl, $lng, $ilCtrl, $ilUser;

        $this->initParticipantEvaluationForm("create");
        if ($this->form->checkInput()) {
            $pe = new cdParticipantEvaluation();
            $pe->setParticipantId((int) $_GET["member_id"]);
            $pe->setCourseId((int) $this->course_id);
            //echo "-".(int) $_GET["member_id"]."-"; exit;
            $pe->setAfterXLessons($_POST["after_x_lessons"]);
            $pe->setStartingCef($_POST["starting_cef"]);
            $pe->setCurrentCef($_POST["current_cef"]);
            
            $pe->setSkills($_POST["skills"]);
            
            $pe->setTestAttendance($_POST["test_attendance"]);
            $pe->setTestResult($_POST["test_result"]);
            $pe->setStayInGroup($_POST["stay_in_group"]);
            $pe->setRecommendedLevel($_POST["recommended_level"]);
            $pe->setTestLevel($_POST["test_level"]);
            
            $pe->create();


            $ilCtrl->setParameter($this, "pe_id", $pe->getId());
            
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "editParticipantEvaluation");
        }

        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHtml());
    }

    /**
     * Update participant evaluation
     */
    public function updateParticipantEvaluation()
    {
        global $lng, $ilCtrl, $tpl;

        $this->initParticipantEvaluationForm("edit");
        if ($this->form->checkInput()) {
            $pe = $this->pe;

            $pe->setAfterXLessons($_POST["after_x_lessons"]);
            $pe->setStartingCef($_POST["starting_cef"]);
            $pe->setCurrentCef($_POST["current_cef"]);
            
            $pe->setSkills($_POST["skills"]);
            
            $pe->setTestAttendance($_POST["test_attendance"]);
            $pe->setTestResult($_POST["test_result"]);
            $pe->setStayInGroup($_POST["stay_in_group"]);
            $pe->setRecommendedLevel($_POST["recommended_level"]);
            $pe->setTestLevel($_POST["test_level"]);
            
            // perform update
            $pe->update();

            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "editParticipantEvaluation");
        }

        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHtml());
    }

    /**
     * Confirm participation evaluation deletion
     */
    public function confirmParticipantEvaluationDeletion()
    {
        global $ilCtrl, $tpl, $lng;

        $lng->loadLanguageModule("meta");

        if (!is_array($_POST["pe_id"]) || count($_POST["pe_id"]) == 0) {
            ilUtil::sendInfo($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listParticipantEvaluations");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $name = ilObjUser::_lookupName((int) $_GET["member_id"]);
            $cgui->setHeaderText(str_replace("%1", $name["firstname"] . " " . $name["lastname"], $this->pl->txt("sure_delete_pe_evaluation")));
            $cgui->setCancel($lng->txt("cancel"), "listParticipantEvaluations");
            $cgui->setConfirm($lng->txt("delete"), "deleteParticipantEvaluation");

            foreach ($_POST["pe_id"] as $i) {
                $pe = new cdParticipantEvaluation($i);
                
                $str = $this->pl->txt("created_on") . ": " . ilDatePresentation::formatDate(new ilDateTime($pe->getCreationDate(), IL_CAL_DATETIME));
                if ($pe->getCreationDate() != $pe->getUpdateDate()) {
                    $str .= ", " .
                        $this->pl->txt("last_update") . ": " . ilDatePresentation::formatDate(new ilDateTime($pe->getUpdateDate(), IL_CAL_DATETIME));
                }
                
                $cgui->addItem("pe_id[]", $i, $str);
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete participant evaluation
     *
     * @param
     * @return
     */
    public function deleteParticipantEvaluation()
    {
        global $ilCtrl, $lng;

        if (is_array($_POST["pe_id"])) {
            foreach ($_POST["pe_id"] as $i) {
                $pe = new cdParticipantEvaluation($i);
                $pe->delete();
            }
        }
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "listParticipantEvaluations");
    }
}
