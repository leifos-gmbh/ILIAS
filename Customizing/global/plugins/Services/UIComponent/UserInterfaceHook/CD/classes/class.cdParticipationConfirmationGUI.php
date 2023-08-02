<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Participation confirmations
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class cdParticipationConfirmationGUI
{
    /**
     * Constructor
     *
     * @param
     * @return
     */
    public function __construct($a_pl)
    {
        global $ilCtrl;
        $this->pl = $a_pl;

        //		$ilCtrl->saveParameter($this, array("ct_id"));

//		$this->readCourseType();
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl;

        $cmd = $ilCtrl->getCmd();
        $this->$cmd();
    }

    /**
     * List all confirmations
     */
    public function listConfirmations()
    {
        global $tpl, $ilToolbar, $ilCtrl;

        $ilToolbar->addButton(
            $this->pl->txt("add_confirmation_template"),
            $ilCtrl->getLinkTarget($this, "addConfirmations")
        );

        $this->pl->includeClass("class.cdConfirmationsTableGUI.php");
        $table = new cdConfirmationsTableGUI($this, "listConfirmations", $this->pl);

        $tpl->setContent($table->getHTML());
    }

    /**
     * Add course type
     */
    public function addConfirmations()
    {
        global $tpl;

        $this->initForm("create");
        $tpl->setContent($this->form->getHTML());
    }

    /**
    * Init  form.
    *
    * @param        int        $a_mode        Edit Mode
    */
    public function initForm($a_mode = "edit")
    {
        global $lng, $ilCtrl;
    
        $this->form = new ilPropertyFormGUI();
    
        // title
        $ti = new ilTextInputGUI($lng->txt("title"), "title");
        $ti->setRequired(true);
        $this->form->addItem($ti);
        
        // file
        $fi = new ilFileInputGUI($lng->txt("file"), "pct_file");
        $fi->setSuffixes(array("rtf"));
        $fi->setRequired(true);
        $this->form->addItem($fi);
    
        // save and cancel commands
        if ($a_mode == "create") {
            $this->form->addCommandButton("saveConfirmation", $lng->txt("save"));
            $this->form->addCommandButton("listConfirmations", $lng->txt("cancel"));
        } else {
            $this->form->addCommandButton("updateConfirmation", $lng->txt("save"));
            $this->form->addCommandButton("listConfirmations", $lng->txt("cancel"));
        }
                    
        $this->form->setTitle($this->pl->txt("new_confirmation_template"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }
    
    /**
     * Save new confirmation form
     *
     */
    public function saveConfirmation()
    {
        global $tpl, $lng, $ilCtrl;
    
        $this->initForm("create");
        if ($this->form->checkInput()) {
            $this->pl->includeClass("class.cdParticipationConfirmation.php");
            cdParticipationConfirmation::addUploadedFile($_FILES["pct_file"]);
            $conf = new cdParticipationConfirmation();
            $conf->setFilename($_FILES["pct_file"]["name"]);
            $conf->setTitle($_POST["title"]);
            $conf->save();
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "listConfirmations");
        } else {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHtml());
        }
    }
    

    /**
     * Confirm confirmation deletion :-)
     */
    public function confirmConfirmationDeletion()
    {
        global $ilCtrl, $tpl, $lng;

        if (!is_array($_POST["ct_id"]) || count($_POST["ct_id"]) == 0) {
            ilUtil::sendInfo($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listCourseTypes");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($this->pl->txt("sure_delete_course_type"));
            $cgui->setCancel($lng->txt("cancel"), "listCourseTypes");
            $cgui->setConfirm($lng->txt("delete"), "deleteCourseType");

            foreach ($_POST["ct_id"] as $i) {
                $cgui->addItem("ct_id[]", $i, cdCourseType::lookupTitle($i));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
    * Confirm template deletion
    */
    public function confirmDeleteConfirmations()
    {
        global $ilCtrl, $tpl, $lng;

        if (!is_array($_POST["conf"]) || count($_POST["conf"]) == 0) {
            ilUtil::sendInfo($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listConfirmations");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($this->pl->txt("sure_delete_confirmations"));
            $cgui->setCancel($lng->txt("cancel"), "listConfirmations");
            $cgui->setConfirm($lng->txt("delete"), "deleteConfirmations");
            
            $this->pl->includeClass("class.cdParticipationConfirmation.php");
            foreach ($_POST["conf"] as $i) {
                $cgui->addItem("conf[]", $i, cdParticipationConfirmation::lookupTitle($i));
            }
            
            $tpl->setContent($cgui->getHTML());
        }
    }
    
    /**
     * Delete confirmations
     *
     * @param
     * @return
     */
    public function deleteConfirmations()
    {
        global $ilCtrl;
        
        if (is_array($_POST["conf"])) {
            foreach ($_POST["conf"] as $i) {
                $this->pl->includeClass("class.cdParticipationConfirmation.php");
                $conf = new cdParticipationConfirmation($i);
                $conf->delete();
            }
            ilUtil::sendSuccess($this->pl->txt("confirmations_deleted"), true);
        }
        
        $ilCtrl->redirect($this, "listConfirmations");
    }
    
    /**
     * Download confirmation template
     *
     * @param
     * @return
     */
    public function downloadConfirmationTemplate()
    {
        $this->pl->includeClass("class.cdParticipationConfirmation.php");
        $conf = new cdParticipationConfirmation((int) $_GET["conf"]);
        $conf->deliver();
    }
}
