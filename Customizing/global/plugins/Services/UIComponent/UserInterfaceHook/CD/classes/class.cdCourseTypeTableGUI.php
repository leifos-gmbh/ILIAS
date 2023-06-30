<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Course type table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class cdCourseTypeTableGUI extends ilTable2GUI
{

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_plugin)
    {
        global $ilCtrl, $lng, $ilAccess, $lng;

        $lng->loadLanguageModule("meta");
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->pl = $a_plugin;

        $this->pl->includeClass("class.cdCourseType.php");
        $this->setData(cdCourseType::getAllCourseTypes());
        $this->setTitle($this->pl->txt("course_types"));

        $this->addColumn("", "", "1");
        $this->addColumn($this->pl->txt("title"));
        $this->addColumn($this->pl->txt("language"));
        $this->addColumn($this->pl->txt("actions"));

        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate($this->pl->getDirectory() . "/templates/tpl.course_type_row.html");
        $this->setEnableTitle(true);
        
        $this->setDefaultOrderField("title");

        $this->addMultiCommand("confirmCourseTypeDeletion", $lng->txt("delete"));
        //$this->addCommandButton("", $this->pl->txt(""));
    }

    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        global $lng, $ilCtrl;

        $this->tpl->setVariable("VAL_TITLE", $a_set["title"]);
        $this->tpl->setVariable("VAL_LANG", $lng->txt("meta_l_" . $a_set["lang"]));
        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        $this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
        $ilCtrl->setParameter($this->parent_obj, "ct_id", $a_set["id"]);
        $this->tpl->setVariable(
            "CMD_EDIT",
            $ilCtrl->getLinkTarget($this->parent_obj, "editCourseType")
        );
    }
}
