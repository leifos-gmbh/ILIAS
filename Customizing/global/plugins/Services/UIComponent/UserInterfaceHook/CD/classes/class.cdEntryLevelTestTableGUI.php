<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Entry level test table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class cdEntryLevelTestTableGUI extends ilTable2GUI
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
        $this->pl->includeClass("class.cdEntryLevelTest.php");
        $this->setData(cdEntryLevelTest::getEntryLevelTestsOfUser($ilUser->getId()));
        $this->setTitle($this->pl->txt("your_recent_language_tests"));

        $this->addColumn("", "", true);
        $this->addColumn($lng->txt("language"), "target_lang");
        $this->addColumn($this->pl->txt("created_on"), "ts");
        $this->addColumn($this->pl->txt("points"), "points");
        $this->addColumn($this->pl->txt("achieved_level"), "result_level");

        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate($this->pl->getDirectory() . "/templates/tpl.entry_level_test_row.html");
        $this->setEnableTitle(true);

        //		$this->addMultiCommand("confirmEntryLevelTestDeletion", $lng->txt("delete"));
        //$this->addCommandButton("", $lng->txt(""));
    }
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        global $lng, $meta;

        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        $this->tpl->setVariable("VAL_CREATED", $a_set["ts"]);
        $this->tpl->setVariable(
            "VAL_LANGUAGE",
            $lng->txt("meta_l_" . $a_set["target_lang"])
        );
        $this->tpl->setVariable("VAL_POINTS", $a_set["points"]);
        $this->tpl->setVariable("VAL_RESULT_LEVEL", $a_set["result_level"]);
    }
}
