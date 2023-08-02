<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Course types
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class cdCourseTypesGUI
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

        $ilCtrl->saveParameter($this, array("ct_id"));

        $this->readCourseType();
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
     * Read course type
     */
    public function readCourseType()
    {
        $this->pl->includeClass("class.cdCourseType.php");
        $this->course_type = new cdCourseType($this->pl, (int) $_GET["ct_id"]);
    }

    /**
     * List all course types
     */
    public function listCourseTypes()
    {
        global $tpl, $ilToolbar, $ilCtrl;

        $ilToolbar->addButton(
            $this->pl->txt("add_course_type"),
            $ilCtrl->getLinkTarget($this, "addCourseType")
        );

        $this->pl->includeClass("class.cdCourseTypeTableGUI.php");
        $table = new cdCourseTypeTableGUI($this, "listCourseTypes", $this->pl);

        $tpl->setContent($table->getHTML());
    }

    /**
     * Add course type
     */
    public function addCourseType()
    {
        global $tpl;

        $this->initCourseTypeForm("create");
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Edit course type
     */
    public function editCourseType()
    {
        global $tpl;

        $this->initCourseTypeForm("edit");
        $this->getCourseTypeValues();
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Init course type form.
     *
     * @param        int        $a_mode        Edit Mode
     */
    public function initCourseTypeForm($a_mode = "edit")
    {
        global $lng, $ilCtrl, $tree;

        $this->form = new ilPropertyFormGUI();

        // title
        $ti = new ilTextInputGUI($lng->txt("title"), "title");
        $ti->setMaxLength(200);
        $ti->setRequired(true);
        $this->form->addItem($ti);

        // language
        $lng->loadLanguageModule("meta");
        $this->pl->includeClass("class.cdLang.php");
        $langs = cdLang::getLanguages();
        $opts = array("" => $lng->txt("please_select"));
        foreach ($langs as $l) {
            $opts[$l] = $lng->txt("meta_l_" . $l);
        }
        $si = new ilSelectInputGUI($lng->txt("language"), "lang");
        $si->setOptions($opts);
        $this->form->addItem($si);

        //var_dump($childs);

        // save and cancel commands
        if ($a_mode == "create") {
            $this->form->addCommandButton("saveCourseType", $lng->txt("save"));
            $this->form->addCommandButton("listCourseTypes", $lng->txt("cancel"));
            $this->form->setTitle($this->pl->txt("add_course_type"));
        } else {
            $this->form->addCommandButton("updateCourseType", $lng->txt("save"));
            $this->form->addCommandButton("listCourseTypes", $lng->txt("cancel"));
            $this->form->setTitle($this->pl->txt("edit_course_type"));
        }

        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    /**
     * Get current values
     */
    public function getCourseTypeValues()
    {
        $values = array();

        $values["title"] = $this->course_type->getTitle();
        $values["lang"] = $this->course_type->getLanguage();

        $this->form->setValuesByArray($values);
    }

    /**
     * Save course type form
     */
    public function saveCourseType()
    {
        global $tpl, $lng, $ilCtrl;

        $this->initCourseTypeForm("create");
        if ($this->form->checkInput()) {
            $this->pl->includeClass("class.cdCourseType.php");
            $course_type = new cdCourseType($this->pl);
            $course_type->setTitle($_POST["title"]);
            $course_type->setLanguage($_POST["lang"]);
            $course_type->create();

            // perform save

            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "listCourseTypes");
        }

        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHtml());
    }

    /**
     * Update course type
     */
    public function updateCourseType()
    {
        global $lng, $ilCtrl, $tpl;

        $this->initCourseTypeForm("edit");
        if ($this->form->checkInput()) {
            // perform update
            $this->course_type->setTitle($_POST["title"]);
            $this->course_type->setLanguage($_POST["lang"]);
            $this->course_type->update();

            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "listCourseTypes");
        }

        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHtml());
    }

    /**
     * Confirm course_type deletion
     */
    public function confirmCourseTypeDeletion()
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
     * Delete course_type
     *
     * @param
     * @return
     */
    public function deleteCourseType()
    {
        global $ilCtrl, $lng;

        if (is_array($_POST["ct_id"])) {
            foreach ($_POST["ct_id"] as $i) {
                $course_type = new cdCourseType($this->pl, $i);
                $course_type->delete();
            }
        }
        ilUtil::sendSuccess("msg_obj_modified");
        $ilCtrl->redirect($this, "listCourseTypes");
    }
}
