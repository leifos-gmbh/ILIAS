<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * CD Trainer GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ilCtrl_Calls ilCDTrainerGUI: ilCDCourseAssignmentGUI
 */
class ilCDTrainerGUI
{
    protected $c_tr_id = 0;	// current trainer id
    protected $user_is_trainer = false;	// is current user trainer (-> only allow to edit own profile)
    protected $former = false; // show former trainers

    /**
     * Constructor
     *
     * @param
     * @return
     */
    public function __construct($a_pl)
    {
        global $ilCtrl, $ilUser;
        
        $this->plugin = $a_pl;
        
        include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
        iljQueryUtil::initjQuery();
        
        $ilCtrl->saveParameter($this, "tr_id");
        $ilCtrl->saveParameter($this, "former");
        $this->former = $_GET["former"];

        include_once("./Services/CD/classes/class.ilCDTrainer.php");
        if (ilCDTrainer::isTrainer($ilUser->getId())) {
            $this->user_is_trainer = true;
            $this->c_tr_id = $ilUser->getId();
        } else {
            $this->c_tr_id = (int) $_GET["tr_id"];
        }
    }
    
    /**
     * Execute command
     *
     * @param
     * @return
     */
    public function executeCommand()
    {
        global $ilCtrl, $tpl, $lng;
        
        $lng->loadLanguageModule("cd");
        if (!$this->user_is_trainer) {
            $tpl->setTitle($lng->txt("trainers"));
            $tpl->setTitleIcon(ilUtil::getImagePath("icon_usr.svg"));
        }
        
        $next_class = $ilCtrl->getNextClass();
        
        switch ($next_class) {
            case "ilcdcourseassignmentgui":
                $ilCtrl->setReturn($this, "listTrainers");
                include_once("./Services/CD/classes/class.ilCDCourseAssignmentGUI.php");
                $cass = new ilCDCourseAssignmentGUI(true);
                $ilCtrl->forwardCommand($cass);
                break;

            default:
                if ($this->user_is_trainer) {
                    $cmd = $ilCtrl->getCmd("editProfile");
                } else {
                    $cmd = $ilCtrl->getCmd("listTrainers");
                }
                if ((!$this->user_is_trainer && in_array($cmd, array("listTrainers", "applyFilter", "resetFilter",
                    "confirmDeactivateTrainer", "confirmActivateTrainer",
                    "activateTrainer", "deactivateTrainer", "showTrainer", "editTrainer", "saveTrainer",
                    "excelExport", "saveNotes", "editNotes", "exportPDFShort", "exportPDFFull",
                    "confirmFormerTrainer", "confirmCurrentTrainer", "formerTrainer", "currentTrainer", "assignCourse"))) ||
                    ($this->user_is_trainer && in_array($cmd, array("editProfile", "saveProfile")))
                ) {
                    $this->$cmd();
                }
        }
        
        $tpl->printToStdOut();
    }
    
    /**
     * List trainers
     *
     * @param
     * @return
     */
    public function listTrainers()
    {
        global $tpl, $ilToolbar, $lng, $ilCtrl;

        $this->setTrainerListTabs();

        $ilToolbar->setFormAction($ilCtrl->getFormAction($this));
        //$ilToolbar->addButton($lng->txt("export_excel"), $ilCtrl->getLinkTarget($this, "excelExport"));

        $cnt = count(ilCDTrainer::getAll(array()));
        $options = array();
        for ($i = 0; $i < $cnt; $i += 100) {
            $options[$i . ":100"] = ($i + 1) . " - " . ($i + 100);
        }
        $options[":"] = " Alle (kann zu Problemen führen)";

        include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
        $si = new ilSelectInputGUI("", "offset_limit");
        $si->setOptions($options);
        $ilToolbar->addInputItem($si);

        $ilToolbar->addFormButton($lng->txt("export_excel"), "excelExport");

        include_once("./Services/CD/classes/class.ilCDTrainerTableGUI.php");
        $tab = new ilCDTrainerTableGUI($this, "listTrainers", $this->plugin, $this->former);
        
        $tpl->setContent($tab->getHTML());
    }
    
    /**
     * Apply filter
     */
    public function applyFilter()
    {
        include_once("./Services/CD/classes/class.ilCDTrainerTableGUI.php");
        $table = new ilCDTrainerTableGUI($this, "listTrainers", $this->plugin, $this->former);
        $table->writeFilterToSession();        // writes filter to session
        $table->resetOffset();                // sets record offest to 0 (first page)
        $this->listTrainers();
    }

    /**
    * Reset filter
    */
    public function resetFilter()
    {
        include_once("./Services/CD/classes/class.ilCDTrainerTableGUI.php");
        $table = new ilCDTrainerTableGUI($this, "listTrainers", $this->plugin, $this->former);
        $table->resetOffset();                // sets record offest to 0 (first page)
        $table->resetFilter();                // clears filter
        $this->listTrainers();
    }

    /**
     * Tabs for trainer list
     *
     * @param
     * @return
     */
    public function setTrainerListTabs()
    {
        global $ilTabs, $ilCtrl, $lng;

        $ilCtrl->setParameter($this, "former", "");
        $ilTabs->addTab(
            "current_trainer",
            $lng->txt("cd_current_trainer"),
            $ilCtrl->getLinkTarget($this, "listTrainers")
        );

        $ilCtrl->setParameter($this, "former", "1");
        $ilTabs->addTab(
            "former_trainer",
            $lng->txt("cd_former_trainer"),
            $ilCtrl->getLinkTarget($this, "listTrainers")
        );

        $ilTabs->activateTab("current_trainer");
        if ($this->former) {
            $ilTabs->activateTab("former_trainer");
        }

        $ilCtrl->setParameter($this, "former", $this->former);
    }


    /**
     * Confirm trainer deactivation
     */
    public function confirmDeactivateTrainer()
    {
        $this->confirmActivateTrainer(true);
    }
    
    /**
     * Confirm trainer (de)activation
     */
    public function confirmActivateTrainer($a_deactivate = false)
    {
        global $ilCtrl, $tpl, $lng;
        
        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($ilCtrl->getFormAction($this));
        if ($a_deactivate) {
            $cgui->setHeaderText($lng->txt("sure_deactivate_trainer"));
            $cgui->setConfirm($lng->txt("deactivate"), "deactivateTrainer");
        } else {
            $cgui->setHeaderText($lng->txt("sure_activate_trainer"));
            $cgui->setConfirm($lng->txt("activate"), "activateTrainer");
        }
        $cgui->setCancel($lng->txt("cancel"), "listTrainers");
            
        $name = ilObjUser::_lookupName($this->c_tr_id);
        $cgui->addItem("tr_id[]", $this->c_tr_id, $name["lastname"] . ", " . $name["firstname"]);
            
        $tpl->setContent($cgui->getHTML());
    }
    
    /**
     * Deactivate trainers
     */
    public function deactivateTrainer()
    {
        $this->activateTrainer(true);
    }
    
    
    /**
     * Activate trainers
     */
    public function activateTrainer($a_deactivate = false)
    {
        global $ilCtrl, $lng;
        
        include_once("./Services/CD/classes/class.ilCDTrainer.php");
        include_once("./Services/CD/classes/class.ilCDPermWrapper.php");
        include_once("./Services/CD/classes/class.cdUtil.php");
        if (cdUtil::isDAF()) {
            $user_admin_centers = ilCDPermWrapper::getAdminCenters();
        } else {
            // fki-patch: trainer mode
            $user_admin_centers = ilCDPermWrapper::getAdminCenters("train");
        }

        if (is_array($_POST["tr_id"])) {
            foreach ($_POST["tr_id"] as $tr_id) {
                $tr_id = (int) $tr_id;
                if (ilObject::_lookupType($tr_id) == "usr") {
                    // check if user may administration trainers of center
                    if (in_array(
                        ilCDTrainer::lookupCenterId($tr_id),
                        $user_admin_centers
                    )) {
                        $usr = new ilObjUser($tr_id);
                        $usr->setActive(!$a_deactivate);
                        $usr->update();
                    }
                }
            }
        }
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "listTrainers");
    }
    
    /**
     * Show trainer
     *
     * @param
     * @return
     */
    public function showTrainer()
    {
        global $ilCtrl, $tpl, $lng, $ilTabs;
        
        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "listTrainers")
        );
        
        ilUtil::sendInfo($lng->txt("no_edit_trainer_permission"));
        
        include_once("./Services/CD/classes/class.ilCDTrainerProfile.php");
        $prof = new ilCDTrainerProfile($this->cd_plugin);
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        include_once("./Services/CD/classes/class.ilCDTrainerProfile.php");
        $up = new ilCDTrainerProfile($this->plugin);
        
        //$user = new ilObjUser($_GET["tr_id"]);
        include_once("./Services/CD/classes/class.ilCDTrainer.php");
        $train = new ilCDTrainer($this->c_tr_id);
        $up->addStandardFieldsToForm($form, null, null, $train);

        $tpl->setContent("<div style='max-width: 800px;'>" . $form->getHTML() . "</div>");
    }

    /**
     * Show trainer
     *
     * @param
     * @return
     */
    public function editTrainer()
    {
        global $ilCtrl, $tpl;

        $form = $this->initTrainerForm();
        $tpl->setContent($form->getHTML());
    }
    
    /**
     * Init trainer form
     */
    public function initTrainerForm($a_mode = "edit")
    {
        global $ilCtrl, $tpl, $lng, $ilTabs;

        if (!$this->user_is_trainer) {
            $ilTabs->setBackTarget(
                $lng->txt("cancel") . " & " . $lng->txt("back"),
                $ilCtrl->getLinkTarget($this, "listTrainers")
            );
        }
        
        include_once("./Services/CD/classes/class.ilCDTrainerProfile.php");
        $prof = new ilCDTrainerProfile($this->cd_plugin);
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        include_once("./Services/CD/classes/class.ilCDTrainerProfile.php");
        $up = new ilCDTrainerProfile($this->plugin);
        
        //$user = new ilObjUser($_GET["tr_id"]);
        include_once("./Services/CD/classes/class.ilCDTrainer.php");
        $train = new ilCDTrainer($this->c_tr_id);
        $up->addStandardFieldsToForm($form, null, null, $train);
        
        if ($a_mode == "edit") {
            if (!$this->user_is_trainer) {
                $form->addCommandButton("saveTrainer", $lng->txt("save"));
            } else {
                $form->addCommandButton("saveProfile", $lng->txt("save"));
            }
        }
        
        return $form;
    }
    
    /**
     * Save trainer
     */
    public function saveTrainer()
    {
        global $tpl, $lng, $ilCtrl;
    
        $form = $this->initTrainerForm("edit");
        if ($form->checkInput()) {
            include_once("./Services/CD/classes/class.ilCDTrainerProfile.php");
            $prof = new ilCDTrainerProfile($this->plugin);
            $tr_id = $this->c_tr_id;
            
            // todo: check center against admin
            
            $user = new ilObjUser($tr_id);
            $prof->updateUserFromForm($form, $user);

            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "listTrainers");
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHtml());
        }
    }

    //
    // Excel export
    //
    
    /**
     * Excel export
     *
     * @param
     * @return
     */
    public function excelExport()
    {
        include_once("./Services/CD/classes/class.ilCDTrainer.php");
        $lo = explode(":", $_POST["offset_limit"]);
        ilCDTrainer::excelExport($this->plugin, (int) $lo[0], (int) $lo[1]);
    }

    //
    // Edit notes
    //
    
    /**
     * Edit notes
     *
     * @param
     * @return
     */
    public function editNotes()
    {
        global $tpl;
        
        $form = $this->initNotesForm();
        
        $tpl->setContent($form->getHTML());
    }
    
    
    /**
     * Init notes form.
     *
     * @param int $a_mode Edit Mode
     */
    public function initNotesForm()
    {
        global $lng, $ilCtrl;
        
        include_once("./Services/CD/classes/class.ilCDTrainer.php");
        $train = new ilCDTrainer($this->c_tr_id);

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        // qm
        $ta = new ilTextAreaInputGUI($lng->txt("cd_qm_evaluation"), "qm");
        $ta->setCols(80);
        $ta->setRows(8);
        $ta->setValue($train->cd_trainer["qm"]);
        $form->addItem($ta);

        // entry date
        include_once("./Services/CD/classes/class.ilCDDateInputGUI.php");
        $ed = new ilCDDateInputGUI($lng->txt("cd_entry_date"), "entry_date");
        $edv = $train->cd_trainer["entry_date"];
        if ($edv != "") {
            $edv = substr($edv, 8, 2) . "." . substr($edv, 5, 2) . "." . substr($edv, 0, 4);
        }
        $ed->setValue($edv);
        $form->addItem($ed);
        
        // fee
        include_once("./Services/CD/classes/class.ilFeeInputGUI.php");
        $fi = new ilFeeInputGUI($lng->txt("cd_fee"), "fee");
        $fi->setMulti(true);
        $fee_val = $fee_dates = array();
        foreach ($train->cd_trainer["fee"] as $f) {
            $fee_val[] = $f["val"];
            $date_val[] = substr($f["date"], 8, 2) . "." . substr($f["date"], 5, 2) . "." . substr($f["date"], 0, 4);
            ;
        }
        $fi->setValue($fee_val);
        $fi->setDateValues($date_val);
        $form->addItem($fi);
        
        // interview
        $iv = new ilTextInputGUI($lng->txt("interview"), "interview");
        $iv->setSize(40);
        $iv->setMaxLength(80);
        $iv->setValue($train->cd_trainer["interview"]);
        $form->addItem($iv);
        
        // competence evaluation
        $options = array(
            "" => $lng->txt("please_select"),
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            );
        $ce = new ilSelectInputGUI($lng->txt("competence_eval"), "competence_eval");
        $ce->setOptions($options);
        $ce->setValue($train->cd_trainer["competence_eval"]);
        $form->addItem($ce);
        
        // appearance
        $options = array(
            "" => $lng->txt("please_select"),
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            );
        $ap = new ilSelectInputGUI($lng->txt("appearance"), "appearance");
        $ap->setOptions($options);
        $ap->setValue($train->cd_trainer["appearance"]);
        $form->addItem($ap);
        
        // overall impression
        $oi = new ilTextAreaInputGUI($lng->txt("overall_impression"), "overall_impression");
        $oi->setCols(80);
        $oi->setRows(8);
        $oi->setValue($train->cd_trainer["overall_impression"]);
        $form->addItem($oi);
        
        // supplier_eval
        $options = array(
            "" => $lng->txt("please_select"),
            "A" => "A",
            "AB" => "AB",
            "B" => "B",
            "C" => "C",
            "N" => "N"
            );
        $se = new ilSelectInputGUI($lng->txt("supplier_eval"), "supplier_eval");
        $se->setOptions($options);
        $se->setValue($train->cd_trainer["supplier_eval"]);
        $form->addItem($se);
        
        // add_arrangement
        $aa = new ilTextInputGUI($lng->txt("add_arrangement"), "add_arrangement");
        $aa->setSize(40);
        $aa->setMaxLength(100);
        $aa->setValue($train->cd_trainer["add_arrangement"]);
        $form->addItem($aa);
        
        // gen_agreement_out
        $go = new ilTextInputGUI($lng->txt("gen_agreement_out"), "gen_agreement_out");
        $go->setSize(40);
        $go->setMaxLength(40);
        $go->setValue($train->cd_trainer["gen_agreement_out"]);
        $form->addItem($go);
        
        // gen_agreement_in
        $gi = new ilTextInputGUI($lng->txt("gen_agreement_in"), "gen_agreement_in");
        $gi->setSize(40);
        $gi->setMaxLength(40);
        $gi->setValue($train->cd_trainer["gen_agreement_in"]);
        $form->addItem($gi);
        
        // train_guide_handout
        $tg = new ilTextInputGUI($lng->txt("train_guide_handout"), "train_guide_handout");
        $tg->setSize(40);
        $tg->setMaxLength(40);
        $tg->setValue($train->cd_trainer["train_guide_handout"]);
        $form->addItem($tg);

        // notes
        $ta = new ilTextAreaInputGUI($lng->txt("cd_other"), "notes");
        $ta->setCols(80);
        $ta->setRows(8);
        $ta->setValue($train->cd_trainer["notes"]);
        $form->addItem($ta);

        // change log
        include_once("./Services/CD/classes/class.ilCDTrainerProfile.php");
        $changes = "";
        $cnt = 0;
        foreach (ilCDTrainerProfile::getChangeLog($this->c_tr_id) as $entry) {
            if ($cnt++ >= 20) {
                continue;
            }
            $changed_by = ilObjUser::_lookupName($entry["changed_by"]);
            $d = new ilDateTime($entry["ts"], IL_CAL_DATETIME);
            $changes .= "<div>" . ilDatePresentation::formatDate($d) . "</div>";
            //$changes.= "<div>".ilDatePresentation::formatDate($d).", ".$changed_by["firstname"]." ".$changed_by["lastname"]."</div>";
        }
        $ne = new ilNonEditableValueGUI($lng->txt("cd_profile_changes"), "", true);
        $ne->setValue($changes);
        $form->addItem($ne);

        // save and cancel commands
        $form->addCommandButton("saveNotes", $lng->txt("save"));
        $form->addCommandButton("listTrainers", $lng->txt("cancel"));
                    
        $name = ilObjUser::_lookupName($this->c_tr_id);
        $form->setTitle($name["lastname"] . ", " . $name["firstname"]);
        $form->setFormAction($ilCtrl->getFormAction($this));
        
        return $form;
    }
    
    /**
     * Save notes form
     */
    public function saveNotes()
    {
        global $tpl, $lng, $ilCtrl;
    
        $form = $this->initNotesForm();
        if ($form->checkInput()) {
            include_once("./Services/CD/classes/class.ilCDTrainer.php");
            $train = new ilCDTrainer($this->c_tr_id);
            $ed = $form->getInput("entry_date");
            if ($ed != "") {
                $ed = explode(".", $ed);
                $ed = $ed[2] . "-" . str_pad($ed[1], 2, "0", STR_PAD_LEFT) . "-" . str_pad($ed[0], 2, "0", STR_PAD_LEFT);
            }
            $train->cd_trainer["entry_date"] = $ed;
            $train->cd_trainer["fee"] = array();
            if (is_array($_POST["fee"])) {
                foreach ($_POST["fee"] as $k => $f) {
                    if ($_POST["fee_d"][$k] != "") {
                        $fed = explode(".", $_POST["fee_d"][$k]);
                        $fed = $fed[2] . "-" . str_pad($fed[1], 2, "0", STR_PAD_LEFT) . "-" . str_pad($fed[0], 2, "0", STR_PAD_LEFT);
                        $train->cd_trainer["fee"][] = array(
                            "val" => $f,
                            "date" => $fed);
                    }
                }
            }

            $fields = array("notes", "qm", "interview", "competence_eval", "appearance",
                "overall_impression","supplier_eval","add_arrangement","gen_agreement_out",
                "gen_agreement_in", "train_guide_handout");
            foreach ($fields as $f) {
                $train->cd_trainer[$f] = $form->getInput($f);
            }

            
            $train->update();

            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "listTrainers");
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHtml());
        }
    }

    ////
    //// Methods used, if current user is trainer (edit own profile)
    ////

    /**
     * Edit profile
     */
    public function editProfile()
    {
        global $tpl;

        $form = $this->initTrainerForm();
        $tpl->setContent($form->getHTML());
    }

    /**
     * Save profile
     */
    public function saveProfile()
    {
        global $tpl, $lng, $ilCtrl;

        $form = $this->initTrainerForm("edit");
        if ($form->checkInput()) {
            include_once("./Services/CD/classes/class.ilCDTrainerProfile.php");
            $prof = new ilCDTrainerProfile($this->plugin);
            $tr_id = $this->c_tr_id;

            // todo: check center against admin

            $user = new ilObjUser($tr_id);
            $prof->updateUserFromForm($form, $user);
            ilCDTrainerProfile::logEditing($tr_id);

            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "editProfile");
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHtml());
        }
    }

    
    //
    // PDF EXPORT
    //
    
    /**
     * Export shortened profile as PDF
     */
    public function exportPDFShort()
    {
        // <item-id>:<nr>[:<custom_title|inline>]
        
        $recipe = array(
            "header1:1"
            ,"usr_cd_sel_country"
            ,"usr_cd_sel_country2"
            
            ,"header4:2"
            ,"usr_mother_tongue:2.1"
            ,"usr_instruction_language:2.2"
            ,"usr_language_skills:2.3:Kenntnisse in anderen Sprachen (Einschätzung nach GER)"
            
            ,"header5:3"
            ,"usr_work_exp_lang_train:3.1"
            ,"usr_work_experience_other:3.2"
            
            ,"header6:4"
            ,"further_education:4.1:inline"
            
            ,"header7:5"
            ,"usr_general_education:5.1:Ausbildung (allgemein)"
            
            ,"header8:6"
            ,":6.1:Trainerkompetenz"
            ,"usr_teaching_experience:6.1.1:Erfahrung mit Unterricht in Allgemeinsprache: Branchen/Zeitraum"
            ,"teach_type:6.1.5:inline"
            ,"usr_other_competences:6.3"
        );
        
        return $this->exportPDF($recipe);
    }
    
    /**
     * Export full profile as PDF
     */
    public function exportPDFFull()
    {
        // <item-id>:<nr>[:<custom_title|inline>]
        
        $recipe = array(
            "header1:1"
            ,"usr_gender"
            ,"usr_title"
            ,"usr_firstname"
            ,"usr_lastname"
            ,"usr_birthday"
            ,"usr_cd_sel_country"
            ,"usr_cd_sel_country2"
            
            ,"header2:1.1"
            ,"usr_street"
            ,"usr_zipcode"
            ,"usr_city"
            ,"usr_country"
            ,"usr_phone_office"
            ,"usr_phone_mobile"
            ,"usr_fax"
            ,"usr_email"
            
            ,"header3:1.2"
            ,"usr_residence_permit"
            ,"usr_work_permit"
            ,"usr_driving_license"
            ,"usr_car_owner"
            
            ,"header4:2"
            ,"usr_mother_tongue:2.1"
            ,"usr_instruction_language:2.2"
            ,"usr_language_skills:2.3:Kenntnisse in anderen Sprachen (Einschätzung nach GER)"
            
            ,"header5:3"
            ,"usr_work_exp_lang_train:3.1"
            ,"usr_work_experience_other:3.2"
            ,"usr_given_lessons:3.3"
            
            ,"header6:4"
            ,"further_education:4.1:inline"
            
            ,"header7:5"
            ,"usr_general_education:5.1:Ausbildung (allgemein)"
            ,"usr_general_education_other:5.2:Sonstiges (allgemein)"
            ,"usr_lang_education:5.3"
            ,"usr_lang_education_other:5.4:Sonstiges (Sprachtrainer)"
            
            ,"header8:6"
            ,":6.1:Trainerkompetenz"
            ,"usr_teaching_experience:6.1:Erfahrung mit Unterricht in Allgemeinsprache: Branchen/Zeitraum"
            ,"teach_exp_tech_lang:6.2:inline"
            ,"usr_other_tech_lang:6.3"
            ,"teach_type:6.4:inline"
            
            ,"header9:7"
            ,"usr_cefr_knowledge:7.1"
            ,"favorite_cefr_levels:7.2"
            ,"cefr_exam_prep_license:7.3"
            ,"cefr_exam_license:7.4"
            ,"usr_other_competences:7.5"
            ,"usr_current_teaching_material:7.6"
            ,"teaching_media:7.7"
            ,"usr_other_teaching_media:7.8"
            ,"usr_teaching_methods:7.9"
            ,"usr_trainer_role:7.10"
            ,"usr_my_potential:7.11"
            ,"usr_scenario:7.12"
            ,"usr_teaching_location:7.13"
        
            ,"header10:8"
            ,"usr_language:8.1"
        );
        
        return $this->exportPDF($recipe);
    }

    /**
     * Export trainer data as PDF
     *
     * @param array $a_recipe
     */
    protected function exportPDF(array $a_recipe)
    {
        $form = $this->initTrainerForm();
        $data = $this->exportPDFProcessFormData($form);
        $html = $this->exportPDFProcessData($a_recipe, $data);

        // display processed item ids for recipe
        foreach (array_keys($data) as $item) {
            // var_dump($item);
        }
        
        include_once "Services/User/classes/class.ilUserUtil.php";
        $trainer = ilUserUtil::getNamePresentation($this->c_tr_id, false, false, "", true);
        $title = "Trainer Export - " . $trainer;

        $tpl = new ilTemplate("tpl.pdf_export.html", true, true, "Services/CD");
        $tpl->setVariable("TITLE", $title);
        $tpl->setVariable("LOGO_URL", ILIAS_HTTP_PATH . "/Customizing/global/skin/cdc/images/HeaderIcon.png");
        $tpl->setVariable("TRAINER_NAME", $trainer);
        $tpl->setVariable("CONTENT", $html);
        $html = $tpl->get();

        if (PATH_TO_HTMLDOC) {
            $src_file = str_replace(".tmp", ".html", ilUtil::ilTempnam());
            if (!stristr($src_file, ".html")) {
                $src_file .= ".html";
            }
            $tgt_file = ilUtil::ilTempnam() . ".pdf";

            file_put_contents($src_file, $html);
            ilUtil::execQuoted(PATH_TO_HTMLDOC, ' --charset utf-8 -f ' . $tgt_file . " " . $src_file);
            unlink($src_file);

            if (file_exists($tgt_file)) {
                ilUtil::deliverFile($tgt_file, $title . ".pdf", "", false, true);
            }
            ilUtil::sendFailure("PDF Export failed");
            $this->listTrainers();
        } else {
            echo $html;
            exit();
        }
    }
    
    /**
     * Extract data from form GUI
     *
     * @param ilPropertyFormGUI $a_form
     * @return array
     */
    protected function exportPDFProcessFormData(ilPropertyFormGUI $a_form)
    {
        $data = array();
        
        $header_idx = 0;
        foreach ($a_form->getItems() as $idx => $item) {
            $this->exportPDFParseInput($data, $idx, $item, $header_idx);
        }
        
        return $data;
    }
    
    /**
     * Extract data from single form input item
     *
     * @param array &$a_data
     * @param int $idx
     * @param ilFormPropertyGUI|ilFormSectionHeaderGUI $a_item
     * @param int $a_header_idx
     * @return array
     */
    protected function exportPDFParseInput(array &$a_data, $idx, $a_item, &$a_header_idx)
    {
        $class = get_class($a_item);
        if ($class == "ilFormSectionHeaderGUI") {
            $a_data[$class . "::header" . (++$a_header_idx)] = array("caption" => $a_item->getTitle());
            return;
        }
        
        if (substr($a_item->getPostVar(), 0, 3) != "fe_") {
            // special hack to group further education checkbox/textarea sub-items
            if (is_array($this->further)) {
                $a_data["ilFurtherEducationGUI::further_education::" . $idx] = array("value" => $this->further);
            }
            $this->further = null;
            
            $id = $class . "::" . $a_item->getPostVar() . "::" . $idx;
        } else {
            // init grouping of further education items
            if (!is_array($this->further)) {
                $this->further = array();
            }
        }

        if ($a_item instanceof ilSubEnabledFormPropertyGUI) {
            $sub_data = array();
            foreach ($a_item->getSubItems() as $sub_idx => $sub_item) {
                $this->exportPDFParseInput($sub_data, $sub_idx, $sub_item, $a_header_idx);
            }
            switch ($class) {
                case "ilNonEditableValueGUI":
                    // used for tech_lang_exp / teach_type_exp
                    if (is_array($sub_data)) {
                        $sub = explode("::", array_shift(array_keys($sub_data)));
                        if ($sub[1]) {
                            $sub = explode("_", $sub[1]);
                            array_pop($sub);
                            $id = $class . "::" . implode("_", $sub) . "::" . $idx;
                            
                            $options = $sub_data;
                            $options = array_shift($options);
                            $options = $options["options"];
                            array_shift($options); // please select
                            $values = array();
                            foreach ($sub_data as $sub_item) {
                                $values[$sub_item["caption"]] = $sub_item["value"];
                            }
                            $a_data[$id] = array(
                                "caption" => $a_item->getTitle(),
                                "value" => $values,
                                "options" => $options
                            );
                        }
                    }
                    // ignore all other
                    return;
                    
                case "ilCheckboxInputGUI":
                    // see above - trying to group further education items
                    if (is_array($this->further)) {
                        $sub_data = array_shift($sub_data);
                        $sub_data["caption"] = $a_item->getTitle();
                        $this->further[] = $sub_data;
                        return;
                    }
                    // fallthrough
                
                    // no break
                default:
                    $a_data = array_merge($a_data, $sub_data);
                    break;
            }
        }
        
        $options = array();
        if (method_exists($a_item, "getOptions")) {
            foreach ($a_item->getOptions() as $option_id => $option) {
                if (is_object($option)) {
                    $options[$option->getValue()] = $option->getTitle();
                } else {
                    $options[$option_id] = $option;
                }
            }
        }

        $checked = "";
        switch ($class) {
            // include levels
            case "ilLangSkillInputGUI":
                $tmp = array();
                $levels = $a_item->getLevelValues();
                foreach ($a_item->getMultiValues() as $idx => $value) {
                    $tmp[$options[$value]] = $levels[$idx];
                }
                $value = $tmp;
                $options = $a_item->getLevelOptions();
                array_shift($options); // please select
                break;
            
            // include dates
            case "ilLangLicenseInputGUI":
                $value = array(
                    "license" => $a_item->getMultiValues(),
                    "date" => $a_item->getDateValues()
                );
                break;

            case "ilBirthdayInputGUI":
            case "ilDateTimeInputGUI":
                $value = $a_item->getDate()
                    ? ilDatePresentation::formatDate($a_item->getDate())
                    : null;
                break;
    
            default:
                if ($class == "ilCheckboxInputGUI") {
                    $checked = $a_item->getChecked();
                }
                $value = $a_item->getValue();
                if ($a_item->getMultiValues()) {
                    $value = $a_item->getMultiValues();
                    if (sizeof($options)) {
                        foreach ($value as $idx => $part) {
                            $value[$idx] = $options[$part];
                        }
                    }
                    $value = array_values($value);
                } else {
                    if (sizeof($options) && $value && !is_array($value)) {
                        $value = $options[$value];
                    }
                }
                break;
        }
                
        $a_data[$id] = array(
                "caption" => $a_item->getTitle(),
                "value" => $value,
                "checked" => $checked,
                "options" => $options
            );
    }
    
    /**
     * Convert form data to simple html
     *
     * @param array $a_recipe
     * @param array $a_data
     * @return string
     */
    protected function exportPDFProcessData(array $a_recipe, array $a_data)
    {
        global $lng;
        
        $res = array();
        $html = "";
        foreach ($a_recipe as $item) {
            $parts = explode(":", $item);
            $id = $parts[0];
            $nr = $parts[1];
            $caption = $parts[2];
            
            $item = $data_id = $data_type = null;
            if ($id) {
                // try to find recipe item in form data
                foreach ($a_data as $data_id => $data_item) {
                    $data_id = explode("::", $data_id);
                    if ($data_id[1] == $id) {
                        $data_type = $data_id[0];
                        $item = $data_item;
                        break;
                    }
                }
            
                if ($item &&
                    !$caption) {
                    $caption = $item["caption"];
                }
            }
            
            $html .= "<div>";
            
            // level / nr / title
            if ($caption != "inline") {
                if ($nr) {
                    $level = sizeof(explode(".", $nr));
                    if ($level == 1) {
                        $nr .= ".";
                    }
                    $html .= "<h" . $level . ">" . $nr . " " . $caption . "</h" . $level . ">\n";
                } else {
                    $html .= "<p class=\"inline\">" . $caption . ": ";
                }
            }
        
            // value / data
            if ($item) {
                // $html .= "[".$data_type."] ";
                
                if ($item["value"]) {
                    switch ($data_type) {
                        // used for tech_lang_exp / teach_type_exp
                        case "ilNonEditableValueGUI":
                            $value = $this->exportPDFTable($item["value"], $item["options"], "&nbsp;", ($caption == "inline") ? $nr : null);
                            break;
                            
                        case "ilLangSkillInputGUI":
                            $value = $this->exportPDFTable($item["value"], $item["options"], $lng->txt("languages"));
                            break;
                        
                        case "ilLangLicenseInputGUI":
                            $value = "<ul>\n";
                            foreach ($item["value"]["license"] as $idx => $license) {
                                $value .= "<li>" . $item["options"][$license] .
                                    " (" . $item["value"]["date"][$idx] . ")</li>\n";
                            }
                            $value .= "</ul>\n";
                            break;
                            
                        // fake input gui to group sub-items
                        case "ilFurtherEducationGUI":
                            $value = "";
                            foreach ($item["value"] as $year) {
                                if ($year["value"]) {
                                    $value .= "<h4>" .
                                        $year["caption"] . "</h4>\n" .
                                        "<p>" . nl2br(trim($year["value"])) . "</p>\n";
                                }
                            }
                            break;
                            
                        case "ilCheckboxInputGUI":
                            $value = $item["checked"]
                                ? $lng->txt("yes")
                                : $lng->txt("no");
                            break;
                                                
                        case "ilSelectInputGUI":
                            $value = !is_array($item["value"])
                                ? $item["value"]
                                : implode(", ", $item["value"]);
                            break;
                        
                        case "ilMultiSelectInputGUI":
                            $value = array();
                            foreach ($item["value"] as $option) {
                                $value[] = $item["options"][$option];
                            }
                            $value = implode(", ", $value);
                            break;
                        
                        default:
                            if (!is_array($item["value"])) {
                                $value = nl2br(trim($item["value"]));
                            } else {
                                // should not be needed
                                $value = print_r($item["value"], true);
                            }
                            break;
                    }
                            
                    if (substr($value, 0, 1) != "<") {
                        if ($nr) {
                            $value = "<p>" . $value . "</p>\n";
                        } else {
                            $value .= "</p>\n";
                        }
                    }
                
                    $html .= $value . "</div>";
                }
            }
        }
        
        return $html;
    }
    
    /**
     * Get option-based values as table matrix
     *
     * @param array $a_values
     * @param array $a_options
     * @param string $a_title
     * @param string $a_nr
     * @return string
     */
    protected function exportPDFTable($a_values, $a_options, $a_title = "&nbsp;", $a_nr = null)
    {
        $html = $a_nr
            ? "<br />"
            : "";
        $html .= "<table><tr>" .
            "<th class=\"table_title\">" . $a_title . "</th>";
        foreach ($a_options as $level) {
            $html .= "<th>" . $level . "</th>";
        }
        $html .= "</tr>";
        if ($a_nr) {
            $nr = explode(".", $a_nr);
            $index = array_pop($nr);
            $nr = implode(".", $nr) . ".";
        }
        foreach ($a_values as $caption => $value) {
            if ($nr) {
                $caption_nr = $nr . ($index++) . " ";
            }
            $html .= "<tr><td class=\"table_title\">" . $caption_nr . $caption . "</td>";
            foreach ($a_options as $option) {
                $html .= "<td>" .
                    ($option == $value
                        ? "X"
                        : "&nbsp;") .
                    "</td>";
            }
            $html .= "</tr>";
        }
        $html .= "</table>";
        return $html;
    }

    //
    // Former/current trainers
    //

    /**
     * Confirm trainer deactivation
     */
    public function confirmFormerTrainer()
    {
        $this->confirmCurrentTrainer(false);
    }

    /**
     * Confirm trainer (de)activation
     */
    public function confirmCurrentTrainer($a_current = true)
    {
        global $ilCtrl, $tpl, $lng;

        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($ilCtrl->getFormAction($this));
        if (!$a_current) {
            $cgui->setHeaderText($lng->txt("cd_sure_former_trainer"));
            $cgui->setConfirm($lng->txt("cd_confirm"), "formerTrainer");
        } else {
            $cgui->setHeaderText($lng->txt("cd_sure_current_trainer"));
            $cgui->setConfirm($lng->txt("cd_confirm"), "currentTrainer");
        }
        $cgui->setCancel($lng->txt("cancel"), "listTrainers");

        $name = ilObjUser::_lookupName($this->c_tr_id);
        $cgui->addItem("tr_id[]", $this->c_tr_id, $name["lastname"] . ", " . $name["firstname"]);

        $tpl->setContent($cgui->getHTML());
    }

    /**
     * Make former trainers
     */
    public function formerTrainer()
    {
        $this->currentTrainer(false);
    }


    /**
     * Make current trainers
     */
    public function currentTrainer($a_current = true)
    {
        global $ilCtrl, $lng;

        include_once("./Services/CD/classes/class.ilCDTrainer.php");
        include_once("./Services/CD/classes/class.ilCDPermWrapper.php");
        include_once("./Services/CD/classes/class.cdUtil.php");
        if (cdUtil::isDAF()) {
            $user_admin_centers = ilCDPermWrapper::getAdminCenters();
        } else {
            // fki-patch: trainer mode
            $user_admin_centers = ilCDPermWrapper::getAdminCenters("train");
        }

        if (is_array($_POST["tr_id"])) {
            foreach ($_POST["tr_id"] as $tr_id) {
                $tr_id = (int) $tr_id;
                if (ilObject::_lookupType($tr_id) == "usr") {
                    // check if user may administration trainers of center
                    if (in_array(
                        ilCDTrainer::lookupCenterId($tr_id),
                        $user_admin_centers
                    )) {
                        $usr = new ilObjUser($tr_id);
                        $usr->setActive($a_current);
                        $usr->update();

                        $train = new ilCDTrainer($tr_id);
                        $train->cd_trainer["former"] = (int) !$a_current;
                        $train->update();
                    }
                }
            }
        }
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "listTrainers");
    }

    /**
     * Assign course
     *
     * @param
     * @return
     */
    public function assignCourse()
    {
        global $ilCtrl;

        if (is_array($_POST["id"]) && count($_POST["id"]) > 0) {
            $ilCtrl->setParameterByClass("ilCDCourseAssignmentGUI", "user_ids", implode(",", $_POST["id"]));
            $ilCtrl->redirectByClass("ilCDCourseAssignmentGUI", "");
        }
    }
}
