<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Center
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class cdCenterGUI
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

        $ilCtrl->saveParameter($this, array("center_id"));

        $this->readCenter();
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
     * Read center
     *
     * @param
     * @return
     */
    public function readCenter()
    {
        $this->pl->includeClass("class.cdCenter.php");
        $this->center = new cdCenter($this->pl, (int) $_GET["center_id"]);
    }

    /**
     * List all center
     *
     * @param
     * @return
     */
    public function listCenters()
    {
        global $tpl, $ilToolbar, $ilCtrl;

        $ilToolbar->addButton(
            $this->pl->txt("add_center"),
            $ilCtrl->getLinkTarget($this, "addCenter")
        );

        $this->pl->includeClass("class.cdCenterTableGUI.php");
        $table = new cdCenterTableGUI($this, "listCenters", $this->pl);

        $tpl->setContent($table->getHTML());
    }

    /**
     * Add center
     */
    public function addCenter()
    {
        global $tpl;

        $this->initCenterForm("create");
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Edit center
     */
    public function editCenter()
    {
        global $tpl;

        $this->initCenterForm("edit");
        $this->getCenterValues();
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Init center form.
     *
     * @param        int        $a_mode        Edit Mode
     */
    public function initCenterForm($a_mode = "edit")
    {
        global $lng, $ilCtrl, $tree;

        $this->form = new ilPropertyFormGUI();

        // title
        $ti = new ilTextInputGUI($lng->txt("title"), "title");
        $ti->setMaxLength(200);
        $ti->setRequired(true);
        $this->form->addItem($ti);

        // center category
        $childs = $tree->getChilds(ROOT_FOLDER_ID);
        $options[""] = $lng->txt("please_select");
        foreach ($childs as $c) {
            if ($c["type"] == "cat") {
                $options[$c["child"]] = $c["title"];
            }
        }
        $si = new ilSelectInputGUI($this->pl->txt("center_category"), "category");
        $si->setOptions($options);
        $this->form->addItem($si);

        // Email
        $ti = new ilTextInputGUI($lng->txt("email"), "email");
        $ti->setMaxLength(200);
        $this->form->addItem($ti);


        //var_dump($childs);

        // save and cancel commands
        if ($a_mode == "create") {
            $this->form->addCommandButton("saveCenter", $lng->txt("save"));
            $this->form->addCommandButton("listCenters", $lng->txt("cancel"));
            $this->form->setTitle($this->pl->txt("add_center"));
        } else {
            $this->form->addCommandButton("updateCenter", $lng->txt("save"));
            $this->form->addCommandButton("listCenters", $lng->txt("cancel"));
            $this->form->setTitle($this->pl->txt("edit_center"));
        }

        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    /**
     * Get current values for center from
     */
    public function getCenterValues()
    {
        $values = array();

        $values["title"] = $this->center->getTitle();
        $values["category"] = $this->center->getCategory();
        $values["email"] = $this->center->getEmail();

        $this->form->setValuesByArray($values);
    }

    /**
     * Save center form
     */
    public function saveCenter()
    {
        global $tpl, $lng, $ilCtrl;

        $this->initCenterForm("create");
        if ($this->form->checkInput()) {
            $this->pl->includeClass("class.cdCenter.php");
            $center = new cdCenter($this->pl);
            $center->setTitle($_POST["title"]);
            $center->setCategory($_POST["category"]);
            $center->setEmail($_POST["email"]);
            $center->create();

            // perform save

            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "listCenters");
        }

        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHtml());
    }

    /**
     * Update center
     */
    public function updateCenter()
    {
        global $lng, $ilCtrl, $tpl;

        $this->initCenterForm("edit");
        if ($this->form->checkInput()) {
            // perform update
            $this->center->setTitle($_POST["title"]);
            $this->center->setCategory($_POST["category"]);
            $this->center->setEmail($_POST["email"]);
            $this->center->update();

            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "listCenters");
        }

        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHtml());
    }

    /**
     * Confirm center deletion
     */
    public function confirmCenterDeletion()
    {
        global $ilCtrl, $tpl, $lng;

        if (!is_array($_POST["center_id"]) || count($_POST["center_id"]) == 0) {
            ilUtil::sendInfo($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listCenters");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($this->pl->txt("sure_delete_center"));
            $cgui->setCancel($lng->txt("cancel"), "listCenters");
            $cgui->setConfirm($lng->txt("delete"), "deleteCenter");

            foreach ($_POST["center_id"] as $i) {
                $cgui->addItem("center_id[]", $i, cdCenter::lookupTitle($i));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete center
     *
     * @param
     * @return
     */
    public function deleteCenter()
    {
        global $ilCtrl, $lng;

        if (is_array($_POST["center_id"])) {
            foreach ($_POST["center_id"] as $i) {
                $center = new cdCenter($this->pl, $i);
                $center->delete();
            }
        }
        ilUtil::sendSuccess("msg_obj_modified");
        $ilCtrl->redirect($this, "listCenters");
    }
}
