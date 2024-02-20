<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * CD needs analysis table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class cdNeedsAnalysisTableGUI extends ilTable2GUI
{

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_pl)
    {
        global $ilCtrl, $lng, $ilAccess, $lng, $ilUser;

        $this->pl = $a_pl;
        $lng->loadLanguageModule("meta");
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->pl->includeClass("class.cdNeedsAnalysis.php");
        $this->setData(cdNeedsAnalysis::getNeedsAnalysesOfUser($ilUser->getId()));
        $this->setTitle($this->pl->txt("your_recent_needs_analyses"));

        $this->addColumn("", "", true);
        $this->addColumn($this->pl->txt("course_lang"), "course_lang");
        $this->addColumn($this->pl->txt("created_on"), "ts");
        $this->addColumn($this->pl->txt("last_update"), "last_update");
        $this->addColumn($lng->txt("actions"));

        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate($this->pl->getDirectory() . "/templates/tpl.needs_analysis_row.html");
        $this->setEnableTitle(true);

        $this->addMultiCommand("confirmNeedsAnalysisDeletion", $lng->txt("delete"));
        //$this->addCommandButton("", $lng->txt(""));
    }

    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        global $lng, $ilCtrl;

        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        $this->tpl->setVariable("VAL_CREATED", $a_set["ts"]);
        if ($a_set["ts"] != $a_set["last_update"]) {
            $this->tpl->setVariable("VAL_UPDATED", $a_set["last_update"]);
        }
        $this->tpl->setVariable("VAL_COURSE_LANGUAGE", $lng->txt("meta_l_" . $a_set["course_lang"]));
        $this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));

        $ilCtrl->setParameterByClass("cdneedsanalysisgui", "na_id", $a_set["id"]);
        $this->tpl->setVariable(
            "CMD_EDIT",
            $ilCtrl->getLinkTargetByClass("cdneedsanalysisgui", "editNeedsAnalysis")
        );
    }
}
