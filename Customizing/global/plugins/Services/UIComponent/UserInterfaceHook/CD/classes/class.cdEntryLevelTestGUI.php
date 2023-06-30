<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Entry level test
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class cdEntryLevelTestGUI
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
        $this->pl->includeClass("class.cdEntryLevelTest.php");
        
        $ilCtrl->saveParameter($this, array("et_id"));

        $this->readEntryLevelTest();
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $ilUser, $tpl;

        $cmd = $ilCtrl->getCmd();

        $tpl->setTitle($this->pl->txt("entry_level_test"));

        // determine default command
        $a = cdEntryLevelTest::getEntryLevelTestsOfUser($ilUser->getId());
        if ($cmd == "") {
            if (count($a) == 0) {
                $cmd = "addEntryLevelTest";
            } else {
                $cmd = "listEntryLevelTests";
            }
        }

        $this->$cmd();
    }

    /**
     * Read entry level test
     */
    public function readEntryLevelTest()
    {
        $this->entry_level_test = new cdEntryLevelTest((int) $_GET["na_id"]);
    }

    /**
     * List all entry level tests
     */
    public function listEntryLevelTests()
    {
        global $tpl, $ilToolbar, $ilCtrl, $lng;

        $ilToolbar->addButton(
            $this->pl->txt("add_entry_level_test"),
            $ilCtrl->getLinkTarget($this, "addEntryLevelTest")
        );

        $this->pl->includeClass("class.cdEntryLevelTestTableGUI.php");
        $table = new cdEntryLevelTestTableGUI($this, "listEntryLevelTests", $this->pl);

        $tpl->setContent($table->getHTML());
    }

    /**
     * Add entry level test
     */
    public function addEntryLevelTest()
    {
        global $tpl, $lng, $ilCtrl;

        $lng->loadLanguageModule("meta");

        cdEntryLevelTest::writeTestSession();

        $ttpl = $this->pl->getTemplate("tpl.list_entry_level_tests.html");
        $test = cdEntryLevelTest::getAvailableTests();
        foreach ($test as $lang => $in_lang) {
            foreach ($in_lang as $il) {
                $ttpl->setCurrentBlock("in_lang");
                $ttpl->setVariable(
                    "IN_LANG",
                    sprintf($this->pl->txt("test_in"), $lng->txt("meta_l_" . $il))
                );
                $back_str = "&il_back=" .
                    urlencode(ILIAS_HTTP_PATH . "/" . $ilCtrl->getLinkTarget($this, "listEntryLevelTests", "", false, false));
                $ttpl->setVariable("HREF", "./etest/pt_" . $lang . "_" . $il .
                    "/index.htm?il_sess_id=" . session_id() . $back_str);
                $ttpl->parseCurrentBlock();
            }
            $ttpl->setCurrentBlock("test");
            $ttpl->setVariable("LANG", $lng->txt("meta_l_" . $lang));
            $ttpl->setVariable("TXT_ENTRY_LEVEL_TEST", $this->pl->txt("entry_level_test"));
            $ttpl->parseCurrentBlock();
        }
        $tpl->setContent($ttpl->get());
    }

    /**
     * Confirm entry level test
     */
    public function confirmEntryLevelTestDeletion()
    {
        global $ilCtrl, $tpl, $lng;

        $lng->loadLanguageModule("meta");

        if (!is_array($_POST["entry_level_test_id"]) || count($_POST["entry_level_test_id"]) == 0) {
            ilUtil::sendInfo($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listEntryLevelTests");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($this->pl->txt("sure_delete_entry_level_test"));
            $cgui->setCancel($lng->txt("cancel"), "listEntryLevelTests");
            $cgui->setConfirm($lng->txt("delete"), "deleteEntryLevelTest");

            foreach ($_POST["entry_level_test_id"] as $i) {
                $cgui->addItem(
                    "entry_level_test_id[]",
                    $i,
                    $lng->txt("meta_l_" . cdEntryLeveltest::lookupLanguage($i)) . ", " .
                    cdEntryLeveltest::lookupCreationDate($i)
                );
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete entry level test
     */
    public function deleteEntryLevelTest()
    {
        global $ilCtrl, $lng;

        if (is_array($_POST["entry_level_test_id"])) {
            foreach ($_POST["entry_level_test_id"] as $i) {
                $entry_level_test = new cdEntryLevelTest($i);
                $entry_level_test->delete();
            }
        }
        ilUtil::sendSuccess("msg_obj_modified");
        $ilCtrl->redirect($this, "listEntryLevelTests");
    }


    ////
    //// Presentation view
    ////

    /**
     * Get presentation view
     *
     * @param
     * @return
     */
    public function getPresentationView($a_user_id)
    {
        $ts = cdEntryLevelTest::getEntryLevelTestsOfUser($a_user_id);

        $html = "";
        foreach ($ts as $t) {
            $this->setEntryLevelTestPresentationForm($t);
            $html .= $this->form->getHTML() . "<br /><br />";
        }

        return $html;
    }

    /**
     * Init entry level test form.
     */
    public function setEntryLevelTestPresentationForm($t)
    {
        global $lng, $ilCtrl;

        $this->form = new ilPropertyFormGUI();

        $lng->loadLanguageModule("meta");

        ilDatePresentation::setUseRelativeDates(false);
        $dates = ", " .
            $lng->txt("created") . ": " .
            ilDatePresentation::formatDate(
                new ilDateTime($t["ts"], IL_CAL_DATETIME)
            );
        ilDatePresentation::setUseRelativeDates(true);

        $this->form->setTitle($this->pl->txt("entry_level_test") . $dates);

        $fields = array(
            "target_lang" => "language",
            "duration" => "duration",
            "finished" => "finished",
            "points" => "points",
            "result_level" => "achieved_level",
            "result_grammar" => "grammar",
            "result_voca" => "voca",
            "result_read" => "read",
            "result_listen" => "listen"
            );

        // leading fields
        foreach ($fields as $f => $l) {
            $ne = new ilNonEditableValueGUI($this->pl->txt($l), $f);
            $v = "";
            switch ($f) {
                case "duration":
                    $hrs = floor($t[$f] / (60 * 60));
                    $mins = floor($t[$f] / (60)) % 60;
                    $sec = $t[$f] % (60);
                    $v = str_pad($hrs, 2, "0", STR_PAD_LEFT) . ":" .
                        str_pad($mins, 2, "0", STR_PAD_LEFT) . ":" .
                        str_pad($sec, 2, "0", STR_PAD_LEFT);
                    break;

                case "language":
                    $v = $lng->txt("meta_l_" . $t[$f]);
                    break;

                case "finished":
                    if ($t[$f]) {
                        $v = $this->pl->txt("yes");
                    } else {
                        $v = $this->pl->txt("no");
                    }
                    break;

                default:
                    $v = $t[$f];
                    break;
            }
            $ne->setValue($v);
            $this->form->addItem($ne);
        }
    }
}
