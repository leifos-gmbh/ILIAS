<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Course extensions
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_isCalledBy cdCourseExtGUI: ilCDUIHookGUI
 */
class cdCourseExtGUI
{
    /**
     * Constructor
     *
     * @param
     * @return
     */
    public function __construct($a_pl)
    {
        $this->pl = $a_pl;
    }
    
    
    /**
     * Modify course toolbar
     *
     * @param object $a_ctb toolbar object
     */
    public static function modifyCourseToolbar($a_ctb, $a_pl)
    {
        global $ilCtrl;

        if (ilObject::_lookupType((int) $_GET["ref_id"], true) !== "crs") {
            return;
        }
        
        $a_ctb->addSeparator();
        
        $a_ctb->addButton(
            $a_pl->txt("generate_part_confirmation"),
            $ilCtrl->getLinkTargetByClass(array("ilcduihookgui", "cdcourseextgui"), "showParticipantConfirmationForm")
        );

        $a_ctb->addSeparator();
        
        $a_ctb->addButton(
            $a_pl->txt("export_evaluations"),
            $ilCtrl->getLinkTargetByClass(array("ilcduihookgui", "cdcourseextgui"), "exportEvaluations")
        );
    }
    
    /**
     * Execute command
     *
     * @param
     * @return
     */
    public function executeCommand()
    {
        global $ilCtrl, $ilTabs;
        
        $next_class = $ilCtrl->getNextClass();
        
        switch ($next_class) {
            default:
                $cmd = $ilCtrl->getCmd();
                $this->$cmd();
                $ilTabs->activateTab('members');
                return;
        }
    }
    
    /**
     * Show participant confirmation form
     *
     * @param
     * @return
     */
    public function showParticipantConfirmationForm()
    {
        global $tpl;

        $this->initParticipantConfirmationForm();
        
        $tpl->setContent($this->form->getHTML());
    }
    
    /**
     * Init participant confirmation form.
     *
     * @param        int        $a_mode        Edit Mode
     */
    public function initParticipantConfirmationForm()
    {
        global $lng, $ilCtrl;
    
        $this->form = new ilPropertyFormGUI();
        
        // select the confirmation template file
        $this->pl->includeClass("class.cdParticipationConfirmation.php");
        $cfiles = cdParticipationConfirmation::getAllConfirmationFiles();
        $options = [];
        foreach ($cfiles as $cf) {
            $options[$cf["id"]] = $cf["title"];
        }
        $si = new ilSelectInputGUI($lng->txt("file"), "conf");
        $si->setOptions($options);
        $this->form->addItem($si);

        // course from
        $ti = new ilTextInputGUI($this->pl->txt("course_from"), "course_from");
        //		$ti->setInfo("xxKursVonxx");
        $this->form->addItem($ti);
        
        // course until
        $ti = new ilTextInputGUI($this->pl->txt("course_until"), "course_until");
        //		$ti->setInfo("xxKursBisxx");
        $this->form->addItem($ti);
        
        // city
        $ti = new ilTextInputGUI($this->pl->txt("city"), "city");
        //		$ti->setInfo("xxOrtxx");
        $this->form->addItem($ti);
        
        // date
        $ti = new ilDateTimeInputGUI($this->pl->txt("date"), "date");
        //		$ti->setInfo("xxDatumxx");
        $this->form->addItem($ti);
        
        // name signature
        $ti = new ilTextInputGUI($this->pl->txt("name_signature"), "name_signature");
        //		$ti->setInfo("xxNameUnterschriftxx");
        $this->form->addItem($ti);
        
        // function
        $ti = new ilTextInputGUI($this->pl->txt("function"), "function");
        //		$ti->setInfo("xxFunktionxx");
        $this->form->addItem($ti);
        
        $this->form->addCommandButton("generateParticipantConfirmationFile", $lng->txt("download"));
        $this->form->addCommandButton("cancelParticipantConfirmation", $lng->txt("cancel"));
                    
        $this->form->setTitle($this->pl->txt("generate_part_confirmation"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }
    
    /**
     * Cancel participation confirmation
     *
     * @param
     * @return
     */
    public function cancelParticipantConfirmation()
    {
        global $ilCtrl;
        
        $ilCtrl->redirectByClass("ilobjcoursegui", "members");
    }
    
    /**
     * Generate participation confirmation zip file
     *
     * @param
     * @return
     */
    public function generateParticipantConfirmationFile()
    {
        $this->pl->includeClass("class.cdParticipationConfirmation.php");

        $this->initParticipantConfirmationForm();
        $this->form->checkInput();
        $date = $this->form->getItemByPostVar("date")->getDate();
        cdParticipationConfirmation::downloadConfirmationFile(
            $this->pl,
            $_GET["ref_id"],
            $_POST["conf"],
            $_POST,
            $date
        );
    }
    
    /**
     * Export evaluations
     *
     * @param
     * @return
     */
    public function exportEvaluations()
    {
        global $lng;
        
        $ref_id = (int) $_GET["ref_id"];
        $obj_id = (int) ilObject::_lookupObjId($ref_id);
        $course = new ilObjCourse($ref_id);

        $excel = new ilExcel();
        $excel->addSheet("Beurteilungen");

        //
        // evaluations
        //

        $this->pl->includeClass("class.cdEvalSkillInputGUI.php");
        $sk_inp = new cdEvalSkillInputGUI("", "", $this->pl);
        $skills = $sk_inp->getSkills();

        // header row
        $col = 0;
        $excel->setCell(1, $col++, $this->pl->txt("creation"));
        $excel->setCell(1, $col++, $this->pl->txt("last_update"));
        $excel->setCell(1, $col++, $this->pl->txt("part_lastname"));
        $excel->setCell(1, $col++, $this->pl->txt("part_firstname"));
        $excel->setCell(1, $col++, $this->pl->txt("trainer"));
        $excel->setCell(1, $col++, $lng->txt("obj_crs"));
        $excel->setCell(1, $col++, $this->pl->txt("course_level"));
        $excel->setCell(1, $col++, $this->pl->txt("after_x_lessons"));
        $excel->setCell(1, $col++, $this->pl->txt("starting_cef"));
        $excel->setCell(1, $col++, $this->pl->txt("current_cef"));
        foreach ($skills as $v => $txt) {
            $excel->setCell(1, $col++, $txt);
        }
        $excel->setCell(1, $col++, $this->pl->txt("test_level"));
        $excel->setCell(1, $col++, $this->pl->txt("test_attendance"));
        $excel->setCell(1, $col++, $this->pl->txt("result"));
        $excel->setCell(1, $col++, $this->pl->txt("stay_in_group"));
        $excel->setCell(1, $col++, $this->pl->txt("recommended_level"));
        
        // get trainers
        $tut = ilCourseParticipants::_getInstanceByObjId($obj_id)->getTutors();
        $tstr = $sep = "";
        if (is_array($tut)) {
            foreach ($tut as $t) {
                $tstr = $sep . ilUserUtil::getNamePresentation($t, false, false, "", true);
                $sep = ", ";
            }
        }

        
        $cnt = 2;
        $this->pl->includeClass("class.cdParticipantEvaluation.php");
        foreach (cdParticipantEvaluation::getPEsOfCourse($obj_id) as $eval) {
            //var_dump($eval);
            $user = ilObjUser::_lookupName($eval["participant_id"]);
            $col = 0;
            $excel->setCell($cnt, $col++, $eval["creation_date"]);
            $excel->setCell($cnt, $col++, $eval["update_date"]);
            $excel->setCell($cnt, $col++, $user["lastname"]);
            $excel->setCell($cnt, $col++, $user["firstname"]);
            $excel->setCell($cnt, $col++, $tstr);
            $excel->setCell($cnt, $col++, ilObject::_lookupTitle($obj_id));
            $excel->setCell($cnt, $col++, $course->getCourseLevel());
            $excel->setCell($cnt, $col++, $eval["after_x_lessons"]);
            $excel->setCell($cnt, $col++, $eval["starting_cef"]);
            $excel->setCell($cnt, $col++, $eval["current_cef"]);
            $sks = unserialize($eval["skills"]);
            foreach ($skills as $v => $txt) {
                $excel->setCell($cnt, $col++, $sks[$v]);
            }
            $excel->setCell($cnt, $col++, $eval["test_level"]);
            $att = $eval["test_attendance"]
                ? "x"
                : "";
            $excel->setCell($cnt, $col++, $att);
            $excel->setCell($cnt, $col++, $eval["test_result"]);
            $stay = $eval["stay_in_group"]
                ? "x"
                : "";
            $excel->setCell($cnt, $col++, $stay);
            $rec_level = $eval["stay_in_group"]
                ? ""
                : $eval["recommended_level"];
            $excel->setCell($cnt, $col++, $rec_level);
            
            $cnt++;
        }

        $exc_name = ilUtil::getASCIIFilename(preg_replace("/\s/", "_", $this->pl->txt("evaluations_course") . "_" . ilObject::_lookupTitle($obj_id)));
        $excel->sendToClient($exc_name);
    }
}
