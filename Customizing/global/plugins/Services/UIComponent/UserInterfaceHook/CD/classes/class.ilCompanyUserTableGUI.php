<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* TableGUI class for user administration
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesUser
*/
class ilCompanyUserTableGUI extends ilTable2GUI
{
    
    /**
    * Constructor
    */
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        $a_pl,
        $a_user_admin_centers,
        $a_company
    ) {
        global $ilCtrl, $lng, $ilAccess, $lng, $rbacsystem;

        $lng->loadLanguageModule("cd");

        $this->user_admin_centers = $a_user_admin_centers;
        $lng->loadLanguageModule("user");
        $lng->loadLanguageModule("meta");
        $this->setId("c_user");
        $this->setShowRowsSelector(true);

        $this->pl = $a_pl;
        $this->company = $a_company;
        $this->pl->includeClass("class.cdNeedsAnalysis.php");
        $this->pl->includeClass("class.cdEntryLevelTest.php");
        $this->pl->includeClass("class.cdOralTarget.php");
        $this->pl->includeClass("class.cdCourseType.php");

        $this->company_id = (int) $_GET["company_id"];
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setTitle($this->lng->txt("users"));
        
        //		$this->setLimit(20);
        
        $this->addColumn("", "", "1", true);
        $this->addColumn($this->lng->txt("lastname"), "lastname");
        $this->addColumn($this->lng->txt("firstname"), "firstname");
        /* RAUS DEZEMBER 2015 - $this->addColumn($this->lng->txt("login"), "login"); */
        $this->addColumn($this->pl->txt("mother_tongue"), "mother_tongue");
        
        /* HELLER */
        $this->addColumn($this->pl->txt("pointscdc"));
        $this->addColumn($this->pl->txt("datumtest"), "last_test");
        /* HELLER */

        
        $this->addColumn($this->pl->txt("train_lang"));
        $this->addColumn($this->pl->txt("avg_self"));
        $this->addColumn($this->pl->txt("online"));
        $this->addColumn($this->pl->txt("oral"));
        $this->addColumn($this->pl->txt("target"));
        $this->addColumn($this->pl->txt("tech_lang"));
        $this->addColumn($this->pl->txt("type_of_training"));
        $this->addColumn($this->lng->txt("actions"));

        $this->setExternalSorting(false);
        $this->setExternalSegmentation(false);
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($this->parent_obj, "applyFilter"));
        $this->setRowTemplate($this->pl->getDirectory() . "/templates/tpl.user_list_row.html");
        //$this->disable("footer");
        $this->setEnableTitle(true);
        $this->initFilter();
        $this->setFilterCommand("applyFilter");
        $this->setDefaultOrderField("login");
        $this->setDefaultOrderDirection("asc");

        $this->setSelectAllCheckbox("id[]");
        $this->setTopCommands(true);


        $this->addMultiCommand("assignCourse", $this->lng->txt("cd_assign_course"));
        $this->addMultiCommand("exportExcelSelected", $this->pl->txt("export_excel"));
        $this->addMultiCommand("confirmChangeCompany", $a_pl->txt("change_company"));
        $this->addMultiCommand("confirmCompanyUserDeletion", $lng->txt("delete"));

        /*		$this->addMultiCommand("activateUsers", $lng->txt("activate"));
                $this->addMultiCommand("deactivateUsers", $lng->txt("deactivate"));
                $this->addMultiCommand("restrictAccess", $lng->txt("accessRestrict"));
                $this->addMultiCommand("freeAccess", $lng->txt("accessFree"));
                $this->addMultiCommand("exportUsers", $lng->txt("export"));
                $this->addCommandButton("importUserForm", $lng->txt("import_users"));
                $this->addCommandButton("addUser", $lng->txt("usr_add"));*/
        
        $this->getItems();
    }

    /**
     * Init filter
     */
    public function initFilter()
    {
        global $lng;

        // title/description
        $ti = new ilTextInputGUI($lng->txt("login") . "/" . $lng->txt("firstname") . "/" . $lng->txt("lastname"), "lastname");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $this->addFilterItem($ti);
        $ti->readFromSession();        // get currenty value from session (always after addFilterItem())
        $this->filter["lastname"] = $ti->getValue();
    }

    /**
    * Get user items
    */
    public function getItems()
    {
        global $lng;
        
        //		$this->determineOffsetAndOrder();
        
        $usr_data = ilUserQuery::getUserListData(
            "login",
            "",
            0,
            999999,
            $this->filter["lastname"],
            $this->filter["activation"],
            $this->filter["last_login"],
            $this->filter["limited_access"],
            $this->filter["no_courses"],
            $this->filter["course_group"],
            $this->filter["global_role"],
            null,
            "",
            null,
            "",
            null,
            $this->company_id
        );
            
        /*
                if (count($usr_data["set"]) == 0 && $this->getOffset() > 0)
                {
                    $this->resetOffset();
                    $usr_data = ilUserQuery::getUserListData(
                        "",
                        "",
                        0,
                        0,
                        $this->filter["lastname"],
                        $this->filter["activation"],
                        $this->filter["last_login"],
                        $this->filter["limited_access"],
                        $this->filter["no_courses"],
                        $this->filter["course_group"],
                        $this->filter["global_role"],
                        null,
                        "",
                        null,
                        "",
                        $this->company_id
                        );
                }*/

        // get last test dates to merge with users
        $tests = cdEntryLevelTest::getLastTestDatesForCompany($this->company_id);

        foreach ($usr_data["set"] as $k => $user) {
            $current_time = time();
            if ($user['active']) {
                if ($user["time_limit_unlimited"]) {
                    $txt_access = $lng->txt("access_unlimited");
                    $usr_data["set"][$k]["access_class"] = "smallgreen";
                } elseif ($user["time_limit_until"] < $current_time) {
                    $txt_access = $lng->txt("access_expired");
                    $usr_data["set"][$k]["access_class"] = "smallred";
                } else {
                    $txt_access = ilDatePresentation::formatDate(new ilDateTime($user["time_limit_until"], IL_CAL_UNIX));
                    $usr_data["set"][$k]["access_class"] = "small";
                }
            } else {
                $txt_access = $lng->txt("inactive");
                $usr_data["set"][$k]["access_class"] = "smallred";
            }
            $usr_data["set"][$k]["access_until"] = $txt_access;
            if (($lt = $tests[(int) $usr_data["set"][$k]["usr_id"]]) != "") {
                $usr_data["set"][$k]["last_test"] = $lt;
            }
        }

        //		$this->setMaxCount($usr_data["cnt"]);
        $this->setData($usr_data["set"]);
    }
    
        
    /**
    * Fill table row
    */
    protected function fillRow($user)
    {
        global $ilCtrl, $lng;

        $data = $this->company->getOverviewData($this->pl, $user["usr_id"]);
        $langs = $data["langs"];
        $tech_lang = $data["tech_lang"];
        $self_avg = $data["self_avg"];
        $test = $data["test"];
        
        /* HELLER */
        $points = $data["points"];
        $ts = $data["ts"];
        
        /* HELLER */
        
        $ots = $data["oral_target"];

        foreach ($langs as $l) {
            $this->tpl->setCurrentBlock("test");
            $this->tpl->setVariable("VAL_TEST", $test[$l] . "&nbsp;");
            $this->tpl->parseCurrentBlock();
            $this->tpl->setCurrentBlock("self_eval");
            $this->tpl->setVariable("VAL_SELF_EVAL", $self_avg[$l] . "&nbsp;");
            $this->tpl->parseCurrentBlock();
            $this->tpl->setCurrentBlock("lang");
            $this->tpl->setVariable("VAL_TRAIN_LANG", $lng->txt("meta_l_" . $l) . "&nbsp;");
            $this->tpl->parseCurrentBlock();
            $this->tpl->setCurrentBlock("tech_lang");
            $this->tpl->setVariable("VAL_TECH_LANG", $tech_lang[$l] . "&nbsp;");
            $this->tpl->parseCurrentBlock();
            $this->tpl->setCurrentBlock("oral");
            $this->tpl->setVariable("VAL_ORAL", $ots[$l]["oral"] . "&nbsp;");
            $this->tpl->parseCurrentBlock();
            $this->tpl->setCurrentBlock("target");
            $this->tpl->setVariable("VAL_TARGET", $ots[$l]["target"] . "&nbsp;");
            $this->tpl->parseCurrentBlock();
            $ct = ($ots[$l]["course_type"] > 0)
                ? cdCourseType::lookupTitle($ots[$l]["course_type"])
                : "";
            $this->tpl->setCurrentBlock("course_type");
            $this->tpl->setVariable("VAL_COURSE_TYPE", $ct . "&nbsp;");
            $this->tpl->parseCurrentBlock();
            
            // Heller
            $this->tpl->setCurrentBlock("points");
            $this->tpl->setVariable("VAL_POINTS", $points[$l] . "&nbsp;");
            $this->tpl->parseCurrentBlock();
            
            $this->tpl->setCurrentBlock("ts");
            $this->tpl->setVariable("VAL_TS", $ts[$l] . "&nbsp;");
            $this->tpl->parseCurrentBlock();
            // Ende
        }

        if ($user["usr_id"] != 6) {
            $this->tpl->setCurrentBlock("checkb");
            $this->tpl->setVariable("ID", $user["usr_id"]);
            $this->tpl->parseCurrentBlock();
        }
        
        $this->tpl->setVariable("VAL_LOGIN", $user["login"]);
        $this->tpl->setVariable("VAL_FIRSTNAME", $user["firstname"]);
        $this->tpl->setVariable("VAL_LASTNAME", $user["lastname"]);
        $this->tpl->setVariable("VAL_EMAIL", $user["email"]);
        $this->tpl->setVariable(
            "VAL_LAST_LOGIN",
            ilDatePresentation::formatDate(new ilDateTime($user['last_login'], IL_CAL_DATETIME))
        );
        $this->tpl->setVariable("VAL_ACCESS_UNTIL", $user["access_until"]);
        $this->tpl->setVariable("CLASS_ACCESS_UNTIL", $user["access_class"]);
        $ilCtrl->setParameter($this->parent_obj, "user_id", $user["usr_id"]);
        $this->tpl->setVariable(
            "HREF_LOGIN",
            $ilCtrl->getLinkTarget($this->parent_obj, "editCompanyUser")
        );

        if (count($this->user_admin_centers) > 0 || ilCDPermWrapper::isPerRole(true)) {
            $this->tpl->setVariable(
                "HREF_LEARN_DATA",
                $ilCtrl->getLinkTarget($this->parent_obj, "showLearnData")
            );
            $this->tpl->setVariable("TXT_LEARN_DATA", $this->pl->txt("learn_data"));

            $this->tpl->setVariable(
                "HREF_ORAL_TARGET",
                $ilCtrl->getLinkTarget($this->parent_obj, "showOralTarget")
            );
            $this->tpl->setVariable("TXT_ORAL_TARGET", $this->pl->txt("oral_target"));

            $this->tpl->setVariable(
                "LP_TARGET",
                $ilCtrl->getLinkTarget($this->parent_obj, "showPartEvalOverview")
            );
            $this->tpl->setVariable("TXT_LP", $this->pl->txt("learning_progress"));
        }

        $ilCtrl->setParameter($this->parent_obj, "user_id", "");
    }
}
