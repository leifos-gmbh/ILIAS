<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Center table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class cdCenterTableGUI extends ilTable2GUI
{

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_plugin)
    {
        global $ilCtrl, $lng, $ilAccess, $lng;

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->pl = $a_plugin;

        $this->pl->includeClass("class.cdCenter.php");
        $this->setData(cdCenter::getAllCenters());
        $this->setTitle($this->pl->txt("centers"));

        $this->addColumn("", "", "1");
        $this->addColumn($this->pl->txt("title"));
        $this->addColumn("ID");
        $this->addColumn($lng->txt("email"));
        $this->addColumn($this->pl->txt("actions"));

        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate($this->pl->getDirectory() . "/templates/tpl.center_row.html");
        $this->setEnableTitle(true);

        $this->addMultiCommand("confirmCenterDeletion", $lng->txt("delete"));
        //$this->addCommandButton("", $this->pl->txt(""));
    }

    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        global $lng, $ilCtrl;

        $this->tpl->setVariable("VAL_TITLE", $a_set["title"]);
        $this->tpl->setVariable("VAL_EMAIL", $a_set["email"]);
        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        $this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
        $ilCtrl->setParameter($this->parent_obj, "center_id", $a_set["id"]);
        $this->tpl->setVariable(
            "CMD_EDIT",
            $ilCtrl->getLinkTarget($this->parent_obj, "editCenter")
        );
    }
}
