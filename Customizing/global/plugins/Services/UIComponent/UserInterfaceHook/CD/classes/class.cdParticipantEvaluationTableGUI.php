<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Participants evaluation table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class cdParticipantEvaluationTableGUI extends ilTable2GUI
{

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_pl, $a_course_id)
    {
        global $ilCtrl, $lng, $ilAccess, $lng, $ilUser;

        $this->pl = $a_pl;
        $this->course_id = $a_course_id;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->pl->includeClass("class.cdParticipantEvaluation.php");
        $this->setData(cdParticipantEvaluation::getPEsOfUser(
            (int) $_GET["member_id"],
            $this->course_id
        ));
        $this->setTitle($this->pl->txt("participant_evaluations") . ", " .
            ilUserUtil::getNamePresentation($_GET["member_id"], false, false, "", true));

        $this->setDefaultOrderField("creation_date");
        $this->setDefaultOrderDirection("desc");
        
        $this->addColumn("", "", true);
        $this->addColumn($this->pl->txt("created_on"), "creation_date");
        $this->addColumn($this->pl->txt("last_update"), "update_date");
        $this->addColumn($lng->txt("actions"));

        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate($this->pl->getDirectory() . "/templates/tpl.pe_row.html");
        $this->setEnableTitle(true);

        $this->addMultiCommand("confirmParticipantEvaluationDeletion", $lng->txt("delete"));
        //$this->addCommandButton("", $lng->txt(""));
    }

    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        global $lng, $ilCtrl;

        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        $this->tpl->setVariable(
            "VAL_CREATED",
            ilDatePresentation::formatDate(new ilDateTime($a_set["creation_date"], IL_CAL_DATETIME))
        );
        if ($a_set["creation_date"] != $a_set["update_date"]) {
            $this->tpl->setVariable(
                "VAL_UPDATED",
                ilDatePresentation::formatDate(new ilDateTime($a_set["update_date"], IL_CAL_DATETIME))
            );
        }
        $this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));

        $ilCtrl->setParameterByClass("cdparticipantevaluationgui", "pe_id", $a_set["id"]);
        $this->tpl->setVariable(
            "CMD_EDIT",
            $ilCtrl->getLinkTargetByClass("cdparticipantevaluationgui", "editParticipantEvaluation")
        );
    }
}
