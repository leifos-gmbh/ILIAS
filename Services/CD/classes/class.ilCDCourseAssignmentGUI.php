<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Course assignment to users
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ilCtrl_Calls ilCDCourseAssignmentGUI:
 */
class ilCDCourseAssignmentGUI
{
    /**
     * @var bool
     */
    protected $as_trainer = false;

    /**
     * Constructor
     *
     */
    public function __construct($a_as_trainer = false)
    {
        global $ilCtrl, $tpl, $lng;

        $lng->loadLanguageModule("cd");

        $this->as_trainer = $a_as_trainer;
        $this->ctrl = $ilCtrl;
        $this->tpl = $tpl;
        $this->lng = $lng;

        $ilCtrl->saveParameter($this, array("user_ids", "sel_ref_id"));
        $this->user_ids = explode(",", $_GET["user_ids"]);
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("selectCourse");

        switch ($next_class) {
            default:
                if (in_array($cmd, array("selectCourse", "selectObject", "cancelAssignment", "assignUsers"))) {
                    $this->$cmd();
                }
        }
    }
    
    /**
     * Select course
     *
     * @param
     * @return
     */
    public function selectCourse()
    {
        ilUtil::sendInfo($this->lng->txt("cd_select_course"));

        include_once("./Services/CD/classes/class.ilCDCourseSelectorGUI.php");
        $exp = new ilCDCourseSelectorGUI($this, "selectCourse");
        $exp->setTypeWhiteList(array("root", "cat", "grp", "crs", "fold"));
        if ($exp->handleCommand()) {
            return;
        }
        $this->tpl->setContent($exp->getHTML());
    }

    /**
     * Confirm
     */
    public function selectObject()
    {
        global $ilCtrl, $tpl, $lng;

        include_once("./Services/User/classes/class.ilUserUtil.php");

        $obj_id = ilObject::_lookupObjId((int) $_GET["sel_ref_id"]);
        $crs_title = ilObject::_lookupTitle($obj_id);

        if (is_array($this->user_ids)) {
            include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $crs_text = " (" . $this->lng->txt("obj_crs") . ": " . $crs_title . ")";
            if ($this->as_trainer) {
                $cgui->setHeaderText($lng->txt("cd_really_assign_as_trainer") . $crs_text);
            } else {
                $cgui->setHeaderText($lng->txt("cd_really_assign_as_member") . $crs_text);
            }
            $cgui->setCancel($lng->txt("cancel"), "cancelAssignment");
            $cgui->setConfirm($lng->txt("cd_assign"), "assignUsers");

            foreach ($this->user_ids as $i) {
                $cgui->addItem("user_id[]", $i, ilUserUtil::getNamePresentation($i, false, false, "", true));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Cancel assignment
     *
     * @param
     * @return
     */
    public function cancelAssignment()
    {
        $this->ctrl->returnToParent($this);
    }

    /**
     * Assign users
     *
     * @param
     * @return
     */
    public function assignUsers()
    {
        include_once("./Modules/Course/classes/class.ilCourseParticipants.php");
        
        $obj_id = ilObject::_lookupObjId((int) $_GET["sel_ref_id"]);
        if (ilObject::_lookupType($obj_id) == "crs") {
            $par = new ilCourseParticipants($obj_id);
            if (is_array($_POST["user_id"])) {
                foreach ($_POST["user_id"] as $user_id) {
                    if (ilObject::_lookupType($user_id) == "usr") {
                        if ($this->as_trainer) {
                            $par->add($user_id, IL_CRS_TUTOR);
                        } else {
                            $par->add($user_id, IL_CRS_MEMBER);
                        }
                    }
                }
            }
        }
        ilUtil::sendInfo($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->returnToParent($this);
    }
}
