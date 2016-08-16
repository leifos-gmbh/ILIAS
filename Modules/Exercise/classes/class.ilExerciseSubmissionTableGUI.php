<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
include_once("./Modules/Exercise/classes/class.ilExAssignmentTeam.php");

/**
 * Exercise submission table 
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesExercise
 */
abstract class ilExerciseSubmissionTableGUI extends ilTable2GUI
{	
	protected $exc; // [ilObjExercise]
	protected $mode; // [int]
	protected $overlay_tpl; // [ilTemplate]
	
	const MODE_BY_ASSIGNMENT = 1;
	const MODE_BY_USER = 2;	
	
	const COLS_MANDATORY = array("name", "status", "mark");
	const COLS_DEFAULT = array("image", "login", "submission_date");
 	const COLS_ORDER = array("image", "name", "login", "type", "team_members", 
			"submission", "idl", "status", "mark", "status_time", 
			"sent_time", "feedback_time", "comment", "notice");
	
	/**
	 * Constructor
	 * 
	 * @param string $a_parent_obj
	 * @param string $a_parent_cmd
	 * @param ilObjExercise $a_exc
	 * @param int $a_item_id
	 * @return self
	 */
	function __construct($a_parent_obj, $a_parent_cmd, ilObjExercise $a_exc, $a_item_id)
	{
		global $ilCtrl;
		
		$this->exc = $a_exc;
		
		$this->initMode($a_item_id);		
		
		parent::__construct($a_parent_obj, $a_parent_cmd);		
				
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));	
		$this->setRowTemplate("tpl.exc_members_row.html", "Modules/Exercise");		
		
		$this->setDefaultOrderField("name");
		$this->setDefaultOrderDirection("asc");		
		
		
		// columns
		
		$this->addColumn("", "", "1", true);
		
		$selected = $this->getSelectedColumns();
		$columns = $this->parseColumns();
		foreach(self::COLS_ORDER as $id)
		{
			if(in_array($id, self::COLS_MANDATORY) ||
				in_array($id, $selected))
			{
				if(array_key_exists($id, $columns))
				{
					$col = $columns[$id];
					$this->addColumn($col[0], $col[1]);
				}
			}
		}
		
		$this->addColumn($this->lng->txt("actions"));
		
		
		// multi actions
		
		$this->addMultiCommand("saveStatusSelected", $this->lng->txt("exc_save_selected"));
		
		// :TODO:
		if($this->ass && 
			$this->ass->hasActiveIDl())
		{
			$this->setFormName("ilExcIDlForm");
			$this->addMultiCommand("setIndividualDeadline", $this->lng->txt("exc_individual_deadline_action"));
		}
		
		$this->addMultiCommand("redirectFeedbackMail", $this->lng->txt("exc_send_mail"));
		$this->addMultiCommand("sendMembers", $this->lng->txt("exc_send_assignment"));
		
		// :TODO:
		if($this->ass && 
			$this->ass->hasTeam())
		{
			$this->addMultiCommand("createTeams", $this->lng->txt("exc_team_multi_create"));
			$this->addMultiCommand("dissolveTeams", $this->lng->txt("exc_team_multi_dissolve"));
		}
		
		$this->addMultiCommand("confirmDeassignMembers", $this->lng->txt("exc_deassign_members"));	
		
		$this->addCommandButton("saveStatusAll", $this->lng->txt("exc_save_all"));			
		
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		include_once "Services/UIComponent/Overlay/classes/class.ilOverlayGUI.php";
		$this->overlay_tpl = new ilTemplate("tpl.exc_learner_comment_overlay.html", true, true, "Modules/Exercise");	
		
		$this->setData($this->parseData());		
	}
	
	abstract protected function initMode($a_item_id);
	
	abstract protected function parseData();	
	
	abstract protected function parseModeColumns();	
		
	function getSelectableColumns()
	{
		$cols = array();
		
		$columns = $this->parseColumns();
		foreach(self::COLS_ORDER as $id)
		{
			if(in_array($id, self::COLS_MANDATORY))
			{
				continue;
			}
			
			if(array_key_exists($id, $columns))
			{
				$col = $columns[$id];
			
				$cols[$id] = array(
					"txt" => $col[0],
					"default" => in_array($id, self::COLS_DEFAULT)
				);
			}
		}
		
		return $cols;
	}
			
	protected function parseColumns()
	{
		$cols = $this->parseModeColumns();
				
		$cols["submission"] = array($this->lng->txt("exc_last_submission"), "submission");			
		
		$cols["status"] = array($this->lng->txt("exc_grade"), "status"); // :TODO:
		$cols["mark"] = array($this->lng->txt("exc_mark"), "mark");			
		$cols["status_time"] = array($this->lng->txt("exc_status_time"), "status_time");	// :TODO:
		
		$cols["sent_time"] = array($this->lng->txt("exc_sent_time"), "sent_time");	 // :TODO:
		
		$cols["feedback_time"] = array($this->lng->txt("exc_feedback_time"), "feedback_time");	 // :TODO:
		$cols["comment"] = array($this->lng->txt("exc_comment_for_learner"), "comment");		
		
		$cols["notice"] = array($this->lng->txt("exc_note_for_tutor"), "note");	
		
		return $cols;
	}	
	
	protected function parseRow($a_user_id, ilExAssignment $a_ass, array $a_row)
	{
		global $ilCtrl, $ilAccess;
				
		$has_no_team_yet = ($a_ass->hasTeam() &&
			!sizeof($a_row["team"]));		
		
		// static columns

		if($this->mode == self::MODE_BY_ASSIGNMENT)
		{								
			if(!array_key_exists("team", $a_row))
			{
				$this->tpl->setVariable("VAL_NAME",	$a_row["name"]);

				// #18327
				if(!$ilAccess->checkAccessOfUser($a_user_id, "read","", $this->exc->getRefId()) &&
					is_array($info = $ilAccess->getInfo()))
				{
					$this->tpl->setCurrentBlock('access_warning');
					$this->tpl->setVariable('PARENT_ACCESS', $info[0]["text"]);
					$this->tpl->parseCurrentBlock();
				}			
			}
			else
			{
				asort($a_row["team"]);
				foreach($a_row["team"] as $team_member_id => $team_member_name) // #10749
				{
					if(sizeof($a_row["team"]) > 1)
					{
						$ilCtrl->setParameterByClass("ilExSubmissionTeamGUI", "id", $team_member_id);
						$url = $ilCtrl->getLinkTargetByClass("ilExSubmissionTeamGUI", "confirmRemoveTeamMember");
						$ilCtrl->setParameterByClass("ilExSubmissionTeamGUI", "id", "");

						include_once "Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php";

						$this->tpl->setCurrentBlock("team_member_removal_bl");
						$this->tpl->setVariable("URL_TEAM_MEMBER_REMOVAL", $url);
						$this->tpl->setVariable("TXT_TEAM_MEMBER_REMOVAL", 
							ilGlyphGUI::get(ilGlyphGUI::CLOSE, $this->lng->txt("remove")));
						$this->tpl->parseCurrentBlock();
					}

					// #18327
					if(!$ilAccess->checkAccessOfUser($team_member_id, "read","", $this->exc->getRefId()) &&
						is_array($info = $ilAccess->getInfo()))
					{
						$this->tpl->setCurrentBlock('team_access_warning');
						$this->tpl->setVariable('TEAM_PARENT_ACCESS', $info[0]["text"]);
						$this->tpl->parseCurrentBlock();
					}		

					$this->tpl->setCurrentBlock("team_member");
					$this->tpl->setVariable("TXT_MEMBER_NAME", $team_member_name);
					$this->tpl->parseCurrentBlock();				
				}

				if($has_no_team_yet)
				{
					// #11957
					$this->tpl->setCurrentBlock("team_info");
					$this->tpl->setVariable("TXT_TEAM_INFO", $this->lng->txt("exc_no_team_yet"));
				}
			}
		}
		else
		{
			$this->tpl->setVariable("VAL_NAME",	$a_row["name"]);
		}
						
		// status
		$this->tpl->setVariable("SEL_".strtoupper($a_row["status"]), ' selected="selected" ');			
		$this->tpl->setVariable("TXT_NOTGRADED", $this->lng->txt("exc_notgraded"));
		$this->tpl->setVariable("TXT_PASSED", $this->lng->txt("exc_passed"));
		$this->tpl->setVariable("TXT_FAILED", $this->lng->txt("exc_failed"));
			
		// mark
		$this->tpl->setVariable("VAL_MARK", $a_row["mark"]
			? ilUtil::prepareFormOutput(trim($a_row["mark"]))
			: "&nbsp;");		
		
		
		// selectable columns
		
		foreach($this->getSelectedColumns() as $col)
		{					
			switch($col)
			{				
				case "image":
					if(!$a_ass->hasTeam())
					{
						include_once "./Services/Object/classes/class.ilObjectFactory.php";							
						if($usr_obj = ilObjectFactory::getInstanceByObjId($a_user_id, false))
						{							
							$this->tpl->setVariable("VAL_IMAGE", $usr_obj->getPersonalPicturePath("xxsmall"));
							$this->tpl->setVariable("TXT_IMAGE", $this->lng->txt("personal_picture"));
						}
					}
					break;
					
				case "team_members":
					if($a_ass->hasTeam())
					{
						if(!sizeof($a_row["team"]))
						{
							$this->tpl->setVariable("VAL_TEAM_MEMBER", $this->lng->txt("exc_no_team_yet"));
						}
						else 
						{																	
							foreach($a_row["team"] as $name)
							{
								$this->tpl->setCurrentBlock("team_member_bl");
								$this->tpl->setVariable("VAL_TEAM_MEMBER", $name);
								$this->tpl->parseCurrentBlock();
							}
						}
					}
					else
					{
						$this->tpl->setVariable("VAL_TEAM_MEMBER", "&nbsp;");
					}
					break;
					
				case "idl":
					$this->tpl->setVariable("VAL_".strtoupper($col), 
						$a_row[$col]
							? ilDatePresentation::formatDate(new ilDateTime($a_row[$col], IL_CAL_UNIX))
							: "&nbsp;");
					break;
				
				case "notice":					
					$this->tpl->setVariable("VAL_".strtoupper($col), $a_row[$col]
						? ilUtil::prepareFormOutput(trim($a_row[$col]))
						: "&nbsp;");
					break;
					
				case "comment":
					$this->tpl->setVariable("VAL_".strtoupper($col), $a_row[$col]
						? nl2br(trim($a_row[$col]))
						: "&nbsp;");
					break;
								
				case "submission":
				case "feedback_time":
				case "status_time":
				case "sent_time":
					$this->tpl->setVariable("VAL_".strtoupper($col), 
						$a_row[$col]
							? ilDatePresentation::formatDate(new ilDateTime($a_row[$col], IL_CAL_DATETIME))
							: "&nbsp;");
					break;
					
				case "login":
					if(!$a_ass->hasTeam())
					{
						continue;
					}
					// fallthrough
				
				default:
					$this->tpl->setVariable("VAL_".strtoupper($col), $a_row[$col]
						? trim($a_row[$col])
						: "&nbsp;");
					break;
			}			
		}
		
		
		// comment overlay
		
		$overlay_id = "excasscomm_".$a_ass->getId()."_".$a_user_id;		
		$overlay = new ilOverlayGUI($overlay_id);
		$overlay->add();

		$lcomment_form = new ilPropertyFormGUI();	
		$lcomment_form->setId($overlay_id);
		$lcomment_form->setPreventDoubleSubmission(false);

		$lcomment = new ilTextAreaInputGUI($this->lng->txt("exc_comment_for_learner"), "lcomment_".$a_ass->getId()."_".$a_user_id);
		$lcomment->setInfo($this->lng->txt("exc_comment_for_learner_info"));
		$lcomment->setValue($a_row["comment"]);
		$lcomment->setCols(45);
		$lcomment->setRows(10);			
		$lcomment_form->addItem($lcomment);

		$lcomment_form->addCommandButton("save", $this->lng->txt("save"));
		// $lcomment_form->addCommandButton("cancel", $lng->txt("cancel"));

		$this->overlay_tpl->setCurrentBlock("overlay_bl");			
		$this->overlay_tpl->setVariable("COMMENT_OVERLAY_ID", $overlay_id);
		$this->overlay_tpl->setVariable("COMMENT_OVERLAY_FORM", $lcomment_form->getHTML());
		$this->overlay_tpl->parseCurrentBlock();
		
		// for js-updating
		$this->tpl->setVariable("LCOMMENT_ID", $overlay_id."_snip");
		
		
		// actions
		
		include_once "Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php";
		$actions = new ilAdvancedSelectionListGUI();
		$actions->setId($a_ass->getId()."_".$a_user_id);
		$actions->setListTitle($this->lng->txt("actions"));
				
		$file_info = $a_row["submission_obj"]->getDownloadedFilesInfoForTableGUIS($this->getParentObject(), $this->getParentCmd());		
		
		$counter = $file_info["files"]["count"];
		if($counter)
		{
			if($file_info["files"]["download_url"])
			{
				$actions->addItem(
					$file_info["files"]["download_txt"]." (".$counter.")",
					"",
					$file_info["files"]["download_url"]
				);
			}
			
			if($file_info["files"]["download_new_url"])
			{
				$actions->addItem(
					$file_info["files"]["download_new_txt"],
					"",
					$file_info["files"]["download_new_url"]
				);				
			}
		}
		
		// feedback mail
		$actions->addItem(
			$this->lng->txt("exc_send_mail"),
			"",
			$ilCtrl->getLinkTarget($this->parent_obj, "redirectFeedbackMail")
		);		
		
		// feedback files		
		include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
		$storage = new ilFSStorageExercise($this->exc_id, $a_ass->getId());
		$counter = $storage->countFeedbackFiles($a_row["submission_obj"]->getFeedbackId());				
		$counter = $counter
			? " (".$counter.")"
			: "";		
		$actions->addItem(
			$this->lng->txt("exc_add_feedback_file").$counter,
			"",
			$ilCtrl->getLinkTargetByClass("ilfilesystemgui", "listFiles")
		);		
		
		// comment (overlay - see above)
		$actions->addItem(
			$this->lng->txt("exc_comment_for_learner_edit"),
			"",
			"#",
			"",
			"",
			"",
			"",
			false,
			"il.Overlay.toggle(event, '".$overlay_id."')"
		);		
		
		// peer review / rating
		if($peer_review = $a_row["submission_obj"]->getPeerReview())
		{									
			$counter = $peer_review->countGivenFeedback(true, $a_user_id);
			$counter = $counter
				? " (".$counter.")"
				: "";	
			$actions->addItem(
				$this->lng->txt("exc_peer_review_given").$counter,
				"",
				$ilCtrl->getLinkTargetByClass("ilexpeerreviewgui", "showGivenPeerReview")
			);	
			
			$counter = sizeof($peer_review->getPeerReviewsByPeerId($a_user_id, true));
			$counter = $counter
				? " (".$counter.")"
				: "";	
			$actions->addItem(
				$this->lng->txt("exc_peer_review_show").$counter,
				"",
				$ilCtrl->getLinkTargetByClass("ilexpeerreviewgui", "showReceivedPeerReview")
			);	
		}
		
		// team
		if($has_no_team_yet)
		{
			$actions->addItem(
				$this->lng->txt("exc_create_team"),
				"",
				$ilCtrl->getLinkTargetByClass("ilExSubmissionTeamGUI", "createSingleMemberTeam")
			);				
		}		
		else if($a_ass->hasTeam())					
		{						
			$actions->addItem(
				$this->lng->txt("exc_team_log"),
				"",
				$ilCtrl->getLinkTargetByClass("ilExSubmissionTeamGUI", "showTeamLog")
			);	
		}									
		
		$this->tpl->setVariable("ACTIONS", $actions->getHTML());
	}
		
	public function render()
	{
		global $ilCtrl;
		
		$url = $ilCtrl->getLinkTarget($this->getParentObject(), "saveCommentForLearners", "", true, false);		
		$this->overlay_tpl->setVariable("AJAX_URL", $url);
		
		return parent::render().
			$this->overlay_tpl->get();
	}
}