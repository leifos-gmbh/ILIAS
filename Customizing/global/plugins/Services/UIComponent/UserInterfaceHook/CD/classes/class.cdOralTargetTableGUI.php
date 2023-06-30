<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * TableGUI class for oral/target marks
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class cdOralTargetTableGUI extends ilTable2GUI
{
    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_pl, $a_user_id)
    {
        global $ilCtrl, $lng, $ilAccess, $lng;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $lng->loadLanguageModule("meta");
        
        $this->user_id = $a_user_id;
        $this->pl = $a_pl;
        $this->pl->includeClass("class.cdNeedsAnalysis.php");
        $this->pl->includeClass("class.cdOralTarget.php");
        
        //$this->setData(cdNeedsAnalysis::getLanguages());

        $this->setData(cdOralTarget::readUserValues($this->user_id));
        $this->setTitle($a_pl->txt("oral_target"));

        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("date"));
        $this->addColumn($this->lng->txt("language"));
        $this->addColumn($a_pl->txt("oral"));
        $this->addColumn($a_pl->txt("target"));
        $this->addColumn($a_pl->txt("course_type"));
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate($this->pl->getDirectory() . "/templates/tpl.oral_target_row.html");

        $this->addMultiCommand("confirmOralTargetDelete", $lng->txt("delete"));
        //		$this->addCommandButton("saveOralTarget", $lng->txt("save"));
        
        /*		$this->lang_levels = array("" => "");
                foreach (cdNeedsAnalysis::getLanguageLevels() as $l)
                {
                    $this->lang_levels[$l] = $l;
                }*/

        $this->pl->includeClass("class.cdCourseType.php");
        $this->pl->includeClass("class.cdLang.php");
        /*		foreach (cdLang::getLanguages() as $l)
                {
                    $this->course_types[$l] = array("" => "");
                }*/
        foreach (cdCourseType::getAllCourseTypes() as $ct) {
            $this->course_types[$ct["id"]] = $ct["title"];
        }
    }
        
    /**
     * Fill table row
     *
     * @param $a_set
     */
    protected function fillRow($a_set)
    {
        global $lng, $ilCtrl;

        $this->tpl->setVariable("DATE", $a_set["date"]);
        $this->tpl->setVariable("LANGUAGE", $lng->txt("meta_l_" . $a_set["lang"]));
        
        //		$v = cdOralTarget::read($this->user_id, $a_set);
        
        // oral
        $this->tpl->setVariable("ORAL_SEL", $a_set["oral"]);
        
        // target
        $this->tpl->setVariable("TARGET_SEL", $a_set["target"]);
        
        // course_type
        $this->tpl->setVariable("COURSE_TYPE", $this->course_types[$a_set["course_type"]]);
        //$this->tpl->setVariable("COURSE_TYPE", $a_set["course_type"]);
        $ilCtrl->setParameter($this->parent_obj, "otid", $a_set["id"]);
        $this->tpl->setVariable("ID", $a_set["id"]);
        $this->tpl->setVariable("HREF_EDIT", $ilCtrl->getLinkTarget($this->parent_obj, "editOralTarget"));
        $this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
    }
}
