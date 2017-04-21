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
	function __construct($a_pl)
	{
		$this->pl = $a_pl;
	}
	
	
	/**
	 * Modify course toolbar
	 *
	 * @param object $a_ctb toolbar object
	 */
	static function modifyCourseToolbar($a_ctb, $a_pl)
	{
		global $ilCtrl;
		
		$a_ctb->addSeparator();
		
		$a_ctb->addButton($a_pl->txt("generate_part_confirmation"),
			$ilCtrl->getLinkTargetByClass(array("ilcduihookgui", "cdcourseextgui"), "showParticipantConfirmationForm"));

		$a_ctb->addSeparator();
		
		$a_ctb->addButton($a_pl->txt("export_evaluations"),
			$ilCtrl->getLinkTargetByClass(array("ilcduihookgui", "cdcourseextgui"), "exportEvaluations"));
	}
	
	/**
	 * Execute command
	 *
	 * @param
	 * @return
	 */
	function executeCommand()
	{
		global $ilCtrl, $ilTabs;
		
		$next_class = $ilCtrl->getNextClass();
		
		switch ($next_class)
		{
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
	function showParticipantConfirmationForm()
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
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		
		// select the confirmation template file
		$this->pl->includeClass("class.cdParticipationConfirmation.php");
		$cfiles = cdParticipationConfirmation::getAllConfirmationFiles();
		foreach ($cfiles as $cf)
		{
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
	function cancelParticipantConfirmation()
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
	function generateParticipantConfirmationFile()
	{
		$this->pl->includeClass("class.cdParticipationConfirmation.php");

		$this->initParticipantConfirmationForm();
		$this->form->checkInput();
		$date = $this->form->getItemByPostVar("date")->getDate();
		cdParticipationConfirmation::downloadConfirmationFile($this->pl, $_GET["ref_id"],
			$_POST["conf"], $_POST, $date);
	}
	
	/**
	 * Export evaluations
	 *
	 * @param
	 * @return
	 */
	function exportEvaluations()
	{
		global $lng;
		
		$ref_id = (int) $_GET["ref_id"];
		$obj_id = (int) ilObject::_lookupObjId($ref_id);
		$course = new ilObjCourse($ref_id);
		
		include_once "./Services/Excel/classes/class.ilExcelWriterAdapter.php";
		$excelfile = ilUtil::ilTempnam();
		$adapter = new ilExcelWriterAdapter($excelfile, FALSE);
		$workbook = $adapter->getWorkbook();
		$workbook->setVersion(8); // Use Excel97/2000 Format
		include_once ("./Services/Excel/classes/class.ilExcelUtils.php");
		
		
		//
		// evaluations
		//
		$ws = $workbook->addWorksheet();
		
		include_once("./Services/Form/classes/class.ilFormPropertyGUI.php");
		$this->pl->includeClass("class.cdEvalSkillInputGUI.php");
		$sk_inp = new cdEvalSkillInputGUI("", "", $this->pl);
		$skills = $sk_inp->getSkills();

		// header row
		$col = 0;
		$ws->writeString(0, $col++, ilExcelUtils::_convert_text($this->pl->txt("creation")));
		$ws->writeString(0, $col++, ilExcelUtils::_convert_text($this->pl->txt("last_update")));
		$ws->writeString(0, $col++, ilExcelUtils::_convert_text($this->pl->txt("part_lastname")));
		$ws->writeString(0, $col++, ilExcelUtils::_convert_text($this->pl->txt("part_firstname")));
		$ws->writeString(0, $col++, ilExcelUtils::_convert_text($this->pl->txt("trainer")));
		$ws->writeString(0, $col++, ilExcelUtils::_convert_text($lng->txt("obj_crs")));
		$ws->writeString(0, $col++, ilExcelUtils::_convert_text($this->pl->txt("course_level")));
		$ws->writeString(0, $col++, ilExcelUtils::_convert_text($this->pl->txt("after_x_lessons")));
		$ws->writeString(0, $col++, ilExcelUtils::_convert_text($this->pl->txt("starting_cef")));
		$ws->writeString(0, $col++, ilExcelUtils::_convert_text($this->pl->txt("current_cef")));
		foreach ($skills as $v => $txt)
		{
			$ws->writeString(0, $col++, ilExcelUtils::_convert_text($txt));
		}
		$ws->writeString(0, $col++, ilExcelUtils::_convert_text($this->pl->txt("test_level")));
		$ws->writeString(0, $col++, ilExcelUtils::_convert_text($this->pl->txt("test_attendance")));
		$ws->writeString(0, $col++, ilExcelUtils::_convert_text($this->pl->txt("result")));
		$ws->writeString(0, $col++, ilExcelUtils::_convert_text($this->pl->txt("stay_in_group")));
		$ws->writeString(0, $col++, ilExcelUtils::_convert_text($this->pl->txt("recommended_level")));
		
		// get trainers
		include_once("./Modules/Course/classes/class.ilCourseParticipants.php");
		include_once("./Services/User/classes/class.ilUserUtil.php");
		$tut = ilCourseParticipants::_getInstanceByObjId($obj_id)->getTutors();
		$tstr = $sep = "";
		if (is_array($tut))
		{
			foreach($tut as $t)
			{
				$tstr = $sep.ilUserUtil::getNamePresentation($t, false, false, "", true);
				$sep = ", ";
			}
		}

		
		$cnt = 1;
		$this->pl->includeClass("class.cdParticipantEvaluation.php");
		foreach (cdParticipantEvaluation::getPEsOfCourse($obj_id) as $eval)
		{
//var_dump($eval);
			$user = ilObjUser::_lookupName($eval["participant_id"]);
			$col = 0;
			$ws->writeString($cnt, $col++, ilExcelUtils::_convert_text($eval["creation_date"]));
			$ws->writeString($cnt, $col++, ilExcelUtils::_convert_text($eval["update_date"]));
			$ws->writeString($cnt, $col++, ilExcelUtils::_convert_text($user["lastname"]));
			$ws->writeString($cnt, $col++, ilExcelUtils::_convert_text($user["firstname"]));
			$ws->writeString($cnt, $col++, ilExcelUtils::_convert_text($tstr));
			$ws->writeString($cnt, $col++, ilExcelUtils::_convert_text(ilObject::_lookupTitle($obj_id)));
			$ws->writeString($cnt, $col++, ilExcelUtils::_convert_text($course->getCourseLevel()));
			$ws->writeString($cnt, $col++, ilExcelUtils::_convert_text($eval["after_x_lessons"]));
			$ws->writeString($cnt, $col++, ilExcelUtils::_convert_text($eval["starting_cef"]));
			$ws->writeString($cnt, $col++, ilExcelUtils::_convert_text($eval["current_cef"]));
			$sks = unserialize($eval["skills"]);
			foreach ($skills as $v => $txt)
			{
				$ws->writeString($cnt, $col++, ilExcelUtils::_convert_text($sks[$v]));
			}
			$ws->writeString($cnt, $col++, ilExcelUtils::_convert_text($eval["test_level"]));
			$att = $eval["test_attendance"]
				? "x"
				: "";
			$ws->writeString($cnt, $col++, ilExcelUtils::_convert_text($att));
			$ws->writeString($cnt, $col++, ilExcelUtils::_convert_text($eval["test_result"]));
			$stay = $eval["stay_in_group"]
				? "x"
				: "";
			$ws->writeString($cnt, $col++, ilExcelUtils::_convert_text($stay));
			$rec_level = $eval["stay_in_group"]
				? ""
				: $eval["recommended_level"];
			$ws->writeString($cnt, $col++, ilExcelUtils::_convert_text($rec_level));
			
			$cnt++;
		}

		$workbook->close();
		$exc_name = ilUtil::getASCIIFilename(preg_replace("/\s/", "_", $this->pl->txt("evaluations_course")."_".ilObject::_lookupTitle($obj_id)));
		ilUtil::deliverFile($excelfile, $exc_name.".xls", "application/vnd.ms-excel");
	}
	

}

?>
