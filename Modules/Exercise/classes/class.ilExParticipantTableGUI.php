<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Modules/Exercise/classes/class.ilExerciseSubmissionTableGUI.php");

/**
* Exercise participant table
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesExercise
*/
class ilExParticipantTableGUI extends ilExerciseSubmissionTableGUI
{
	protected $user; // [ilObjUser]
	
	protected function initMode($a_item_id)
	{				
		global $lng, $ilCtrl;
		
		$this->mode = self::MODE_BY_USER;
		
		// global id for all exercises
		$this->setId("exc_part");
		
		if($a_item_id > 0)
		{
			$name = ilObjUser::_lookupName($a_item_id);
			if(trim($name["login"]))
			{				
				$this->user = new ilObjUser($a_item_id);
								
				$this->setTitle($lng->txt("exc_participant").": ".
					$name["lastname"].", ".$name["firstname"]." [".$name["login"]."]");		
			}			
		}				
		
		$this->setSelectAllCheckbox("ass");			
	}		
	
	protected function parseData()
	{
		global $ilAccess, $ilCtrl;
		
		// #14650 - invalid user
		if(!$this->user)
		{
			$ilCtrl->setParameter($this->getParentObject(), "member_id", "");
			$ilCtrl->redirect($this->getParentObject(), $this->getParentCmd());
		}
		
		// #18327
		if(!$ilAccess->checkAccessOfUser($this->user->getId(), "read","", $this->exc->getRefId()) &&
			is_array($info = $ilAccess->getInfo()))
		{
			$this->setDescription('<span class="warning">'.$info[0]['text'].'</span>');
		}		
		
		// see ilExAssignmentEditorGUI
		$types_map = array(
			ilExAssignment::TYPE_UPLOAD => $this->lng->txt("exc_type_upload"),
			ilExAssignment::TYPE_UPLOAD_TEAM => $this->lng->txt("exc_type_upload_team"),
			ilExAssignment::TYPE_BLOG => $this->lng->txt("exc_type_blog"),
			ilExAssignment::TYPE_PORTFOLIO => $this->lng->txt("exc_type_portfolio"),
			ilExAssignment::TYPE_TEXT => $this->lng->txt("exc_type_text"),
			);
		
		$data = array();		
		foreach(ilExAssignment::getInstancesByExercise($this->exc->getId()) as $ass)
		{				
			// ilExAssignment::getMemberListData()
			
			$member_status = $ass->getMemberStatus($this->user->getId());
			$submission = new ilExSubmission($ass, $this->user->getId());
			
			$row = array(
				"ass" => $ass,
				"type" => $types_map[$ass->getType()],
				"submission_obj" => $submission,
				"name" => $ass->getTitle(),
				"status" => $member_status->getStatus(),
				"mark" => $member_status->getMark(),
				"sent_time" => $member_status->getSentTime(),
				"status_time" => $member_status->getStatusTime(),
				"feedback_time" => $member_status->getFeedbackTime(),
				"submission" => $submission->getLastSubmission(),				
				"notice" => $member_status->getNotice(),
				"comment" => $member_status->getComment()				
			);
			
			if($ass->hasTeam())
			{
				$row["team"] = array();
				foreach($submission->getTeam()->getMembers() as $user_id)
				{
					$row["team"][$user_id] = ilObjUser::_lookupFullname($user_id);
				}
				asort($row["team"]);
			}
			
			$data[] = $row;
		}
							
		return $data;		
	}			
	
	protected function parseModeColumns()
	{
		$cols = array();
				
		$cols["name"] = array($this->lng->txt("exc_assignment"), "name");	
		$cols["type"] = array($this->lng->txt("type"), "type");	
		$cols["team_members"] = array($this->lng->txt("exc_team"));			
		
		return $cols;
	}	
	
	protected function fillRow($a_item)
	{
		global $ilCtrl;
		
		$ilCtrl->setParameter($this->parent_obj, "member_id", $this->user->getId());
		$ilCtrl->setParameter($this->parent_obj, "ass_id", $a_item["ass"]->getId());
				
		// multi-select id
		$this->tpl->setVariable("VAL_ID", $a_item["ass"]->getId());			
		
		$this->parseRow($this->user->getId(), $a_item["ass"], $a_item);									
			
		$ilCtrl->setParameter($this->parent_obj, "ass_id", "");
		$ilCtrl->setParameter($this->parent_obj, "member_id", $this->user->getId());			
	}	
}