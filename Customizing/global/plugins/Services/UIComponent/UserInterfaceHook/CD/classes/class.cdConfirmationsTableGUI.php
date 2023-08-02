<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * TableGUI class for participation confirmation
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class cdConfirmationsTableGUI extends ilTable2GUI
{
    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_plugin)
    {
        global $ilCtrl, $lng, $ilAccess, $lng;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->pl = $a_plugin;
        
        $this->pl->includeClass("class.cdParticipationConfirmation.php");
        $this->setData(cdParticipationConfirmation::getAllConfirmationFiles());
        $this->setTitle($this->pl->txt("confirmation_templates"));
        
        $this->addColumn("", "", "1%", true);
        $this->addColumn($this->lng->txt("title"));
        $this->addColumn($this->lng->txt("file"));
        $this->addColumn($this->lng->txt("actions"));
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate($this->pl->getDirectory() . "/templates/tpl.confirmation_row.html");

        $this->addMultiCommand("confirmDeleteConfirmations", $lng->txt("delete"));
        //		$this->addCommandButton("", $lng->txt(""));
    }
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        global $lng, $ilCtrl;

        $ilCtrl->setParameter($this->parent_obj, "conf", $a_set["id"]);
        $this->tpl->setCurrentBlock("cmd");
        $this->tpl->setVariable("TXT_CMD", $lng->txt("download"));
        $this->tpl->setVariable("HREF_CMD", $ilCtrl->getLinkTarget($this->parent_obj, "downloadConfirmationTemplate"));
        $this->tpl->parseCurrentBlock();
        $ilCtrl->setParameter($this->parent_obj, "conf", "");
        
        $this->tpl->setVariable("ID", $a_set["id"]);
        $this->tpl->setVariable("TITLE", $a_set["title"]);
        $this->tpl->setVariable("FILENAME", $a_set["filename"]);
    }
}
