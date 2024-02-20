<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for trainers
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class ilCDTrainerTableGUI extends ilTable2GUI
{
    protected $former = false; // show former trainers

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_plugin, $a_former)
    {
        global $ilCtrl, $lng, $ilAccess, $lng;

        $this->former = $a_former;

        $this->setId("cdtrain");
        $this->setShowRowsSelector(true);
        
        $this->plugin = $a_plugin;
        
        include_once("./Services/CD/classes/class.ilCDPermWrapper.php");
        // fki-patch: trainer mode
        $this->user_admin_centers = ilCDPermWrapper::getAdminCenters("train");
        
        $this->plugin->includeClass("class.cdLang.php");
        
        include_once("./Services/CD/classes/class.ilCDTechnicalLanguage.php");
        $this->tech_lang = ilCDTechnicalLanguage::getAll();

        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        include_once("./Services/CD/classes/class.ilCDTrainer.php");
        $this->setTitle($lng->txt("trainers"));

        if (!$this->former) {
            $this->addColumn("", "", true);
        }

        $this->addColumn($this->lng->txt("name"), "name");
        $this->addColumn($this->lng->txt("cd_registration"), "create_date");
        // HELLER DEZEMBER 2015 - $this->addColumn($this->lng->txt("login"), "login");
        $this->addColumn($this->lng->txt("zipcode"), "zipcode");
        $this->addColumn($this->lng->txt("city"), "city");
        $this->addColumn($this->lng->txt("mother_tongue"), "mother_tongue");
        $this->addColumn($this->lng->txt("instruction_language"), "instruction_language");
        $this->addColumn($this->lng->txt("cd_sel_country"), "sel_country");
        // HELLER DEZEMBER 2015 - $this->addColumn($this->lng->txt("cd_entry_date"), "entry_date");
        $this->addColumn($this->lng->txt("cd_fee"));
        $this->addColumn($this->lng->txt("tech_langs"), "tech_lang");
        
        $this->addColumn($this->plugin->txt("center"), "center_id");
        $this->addColumn($this->lng->txt("active"), "active");
        $this->addColumn($this->lng->txt("actions"));

        
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.trainer_row.html", "Services/CD");

        if (!$this->former) {
            $this->addMultiCommand("assignCourse", $lng->txt("cd_assign_course"));
        }

        //		$this->addCommandButton("", $lng->txt(""));
        $this->initFilter();

        // get current or former trainers
        $filter = $this->filter;
        if ($this->former) {
            $filter["former"] = 1;
        }
        $this->setData(ilCDTrainer::getAll($filter));
        
        $this->plugin->includeClass("class.cdCenter.php");
    }

    /**
     * Init filter
     */
    public function initFilter()
    {
        global $lng;

        include_once("./Services/Form/classes/class.ilTextInputGUI.php");
        include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
        
        // name
        $ti = new ilTextInputGUI($lng->txt("name"), "name");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $this->addFilterItem($ti);
        $ti->readFromSession();        // get currenty value from session (always after addFilterItem())
        $this->filter["name"] = $ti->getValue();

        // zipcode
        $ti = new ilTextInputGUI($lng->txt("zipcode"), "zipcode");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $this->addFilterItem($ti);
        $ti->readFromSession();        // get currenty value from session (always after addFilterItem())
        $this->filter["zipcode"] = $ti->getValue();

        // city
        $ti = new ilTextInputGUI($lng->txt("city"), "city");
        $ti->setMaxLength(64);
        $ti->setSize(20);
        $this->addFilterItem($ti);
        $ti->readFromSession();        // get currenty value from session (always after addFilterItem())
        $this->filter["city"] = $ti->getValue();

        // language
        $lng->loadLanguageModule("meta");
        $options = cdLang::getLanguagesAsOptions("filter");
        include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
        $si = new ilSelectInputGUI($lng->txt("instruction_language"), "instr_lang");
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();        // get currenty value from session (always after addFilterItem())
        $this->filter["instr_lang"] = $si->getValue();
        
        // center
        $this->plugin->includeClass("class.cdCenter.php");
        $options = array(0 => $lng->txt("all"));
        foreach (cdCenter::getAllCenters() as $c) {
            $options[$c["id"]] = $c["title"];
        }
        $si = new ilSelectInputGUI($this->plugin->txt("center"), "center_id");
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();        // get currenty value from session (always after addFilterItem())
        $this->filter["center_id"] = $si->getValue();

        // registration
        include_once("./Services/Form/classes/class.ilDateDurationInputGUI.php");
        $reg = new ilDateDurationInputGUI($lng->txt("cd_registration"), "create_date");
        $this->addFilterItem($reg);
        $reg->readFromSession();

        // tech_langs
        include_once("./Services/CD/classes/class.ilCDTechnicalLanguage.php");
        $options = array(0 => $lng->txt("all"));
        foreach (ilCDTechnicalLanguage::getAll() as $k => $l) {
            $options[$k] = $this->plugin->txt($l);
        }
        $si = new ilSelectInputGUI($this->plugin->txt("tech_lang"), "tech_lang");
        $si->setOptions($options);
        $this->addFilterItem($si);
        $si->readFromSession();        // get currenty value from session (always after addFilterItem())
        $this->filter["tech_lang"] = $si->getValue();


        $this->filter["create_date_start"] = "";
        $this->filter["create_date_end"] = "";
        $s = $reg->getStart();
        $e = $reg->getEnd();
        if (is_object($s)) {
            $this->filter["create_date_start"] = $s->get(IL_CAL_DATE);
        }
        if (is_object($e)) {
            $this->filter["create_date_end"] = $e->get(IL_CAL_DATE);
        }
    }
    
    
    /**
     * Fill table row
     * Editiert von Heller 06-09-2015 - Übersetzung der Variablen
     */
    protected function fillRow($a_set)
    {
        global $lng, $ilCtrl;

        $ilCtrl->setParameter($this->parent_obj, "tr_id", $a_set["id"]);

        if (!$this->former) {
            $this->tpl->setCurrentBlock("cb");
            $this->tpl->setVariable("ID", $a_set["usr_id"]);
            $this->tpl->parseCurrentBlock();
        }

        if (in_array($a_set["center_id"], $this->user_admin_centers)) {
            if ($a_set["active"]) {
                $this->tpl->setCurrentBlock("cmd");
                $this->tpl->setVariable(
                    "HREF_CMD",
                    $ilCtrl->getLinkTarget($this->parent_obj, "confirmDeactivateTrainer")
                );
                $this->tpl->setVariable("CMD", $lng->txt("cd_deactivate"));
                $this->tpl->parseCurrentBlock();
            } elseif (!$this->former) {
                $this->tpl->setCurrentBlock("cmd");
                $this->tpl->setVariable(
                    "HREF_CMD",
                    $ilCtrl->getLinkTarget($this->parent_obj, "confirmActivateTrainer")
                );
                $this->tpl->setVariable("CMD", $lng->txt("cd_activate"));
                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setCurrentBlock("cmd");
            $this->tpl->setVariable(
                "HREF_CMD",
                $ilCtrl->getLinkTarget($this->parent_obj, "editTrainer")
            );
            $this->tpl->setVariable("CMD", $lng->txt("cd_edit"));
            $this->tpl->parseCurrentBlock();

            $this->tpl->setCurrentBlock("cmd");
            $this->tpl->setVariable(
                "HREF_CMD",
                $ilCtrl->getLinkTarget($this->parent_obj, "editNotes")
            );
            $this->tpl->setVariable("CMD", $lng->txt("cd_notes"));
            $this->tpl->parseCurrentBlock();
                        
            if (PATH_TO_HTMLDOC) {
                $this->tpl->setCurrentBlock("cmd");
                $this->tpl->setVariable(
                    "HREF_CMD",
                    $ilCtrl->getLinkTarget($this->parent_obj, "exportPDFShort")
                );
                $this->tpl->setVariable("CMD", $lng->txt("pdf_export_short"));
                $this->tpl->parseCurrentBlock();
                
                $this->tpl->setCurrentBlock("cmd");
                $this->tpl->setVariable(
                    "HREF_CMD",
                    $ilCtrl->getLinkTarget($this->parent_obj, "exportPDFFull")
                );
                $this->tpl->setVariable("CMD", $lng->txt("pdf_export_full"));
                $this->tpl->parseCurrentBlock();
            }

            if (!$this->former) {
                $this->tpl->setCurrentBlock("cmd");
                $this->tpl->setVariable(
                    "HREF_CMD",
                    $ilCtrl->getLinkTarget($this->parent_obj, "confirmFormerTrainer")
                );
                $this->tpl->setVariable("CMD", $lng->txt("cd_make_former_trainer"));
                $this->tpl->parseCurrentBlock();
            } else {
                $this->tpl->setCurrentBlock("cmd");
                $this->tpl->setVariable(
                    "HREF_CMD",
                    $ilCtrl->getLinkTarget($this->parent_obj, "confirmCurrentTrainer")
                );
                $this->tpl->setVariable("CMD", $lng->txt("cd_make_current_trainer"));
                $this->tpl->parseCurrentBlock();
            }
        } else {
            $this->tpl->setCurrentBlock("cmd");
            $this->tpl->setVariable(
                "HREF_CMD",
                $ilCtrl->getLinkTarget($this->parent_obj, "showTrainer")
            );
            $this->tpl->setVariable("CMD", $lng->txt("show"));
            $this->tpl->parseCurrentBlock();
        }
            
        if (is_array($a_set["technical_languages_arr"])) {
            foreach ($a_set["technical_languages_arr"] as $tl) {
                if ($tl["val"] > 1) {
                    $this->tpl->setCurrentBlock("tl");
                    $this->tpl->setVariable(
                        "TECHNICAL_LANGUAGE",
                        $this->plugin->txt($this->tech_lang[$tl["disc"]])
                    );
                    $this->tpl->setVariable(
                        "AMOUNT",
                        $tl["val"] == 2 ? $lng->txt("little"): $lng->txt("good")
                    );
                    $this->tpl->parseCurrentBlock();
                }
                //				var_dump($tl);
            }
        }
        
        if ($a_set["active"]) {
            $this->tpl->setVariable("ACTIVE", $lng->txt("cd_trainer_yes"));
        } else {
            $this->tpl->setVariable("ACTIVE", $lng->txt("cd_trainer_no"));
        }
        $this->tpl->setVariable("CENTER", cdCenter::lookupTitle($a_set["center_id"]));
        
        $this->tpl->setVariable("NAME", $a_set["name"]);
        $this->tpl->setVariable(
            "CREATE_DATE",
            ilDatePresentation::formatDate(new ilDateTime($a_set["create_date"], IL_CAL_DATETIME))
        );
        $this->tpl->setVariable("LOGIN", $a_set["login"]);
        
        $this->tpl->setVariable("ZIPCODE", $a_set["zipcode"]);
        $this->tpl->setVariable("CITY", $a_set["city"]);
        $this->tpl->setVariable("MOTHER_TONGUES", $a_set["mother_tongues_txt"]);
        $this->tpl->setVariable("INSTRUCTION_LANGUAGES", $a_set["instruction_languages_txt"]);
        if ($a_set["sel_country"] != "") {
            $add = ($a_set["cd_sel_country2"] == "")
                ? ""
                : ", <br>" . $lng->txt("meta_c_" . $a_set["cd_sel_country2"]);
            $this->tpl->setVariable("SEL_COUNTRY", $lng->txt("meta_c_" . $a_set["sel_country"]) . $add);
        }
        $ed = $a_set["entry_date"];
        if ($ed != "") {
            $ed = explode("-", $ed);
            $this->tpl->setVariable("ENTRY_DATE", $ed[2] . "." . $ed[1] . "." . $ed[0]);
        }
        $a_set["fee"] = unserialize($a_set["fee"]);
        if (is_array($a_set["fee"]) && count($a_set["fee"]) > 0) {
            $fee = $a_set["fee"][count($a_set["fee"]) - 1];
            $f = explode("-", $fee["date"]);
            $this->tpl->setVariable("FEE", $fee["val"] . "<br />(" . $f[2] . "." . $f[1] . "." . $f[0] . ")");
        }
        
        $ilCtrl->setParameter($this->parent_obj, "tr_id", $_GET["tr_id"]);
    }
}
