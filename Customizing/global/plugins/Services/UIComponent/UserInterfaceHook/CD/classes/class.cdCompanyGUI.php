<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Company
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_Calls cdCompanyGUI: ilCDCourseAssignmentGUI
 */
class cdCompanyGUI
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
        $this->pl->includeClass("class.cdCompany.php");
        $ilCtrl->saveParameter($this, array("company_id", "user_id", "otid"));

        $this->user_id = 0;
        if (ilCDPermWrapper::canAdminUser((int) $_GET["user_id"], $this->pl)) {
            $this->user_id = (int) $_GET["user_id"];
        }


        $this->user_admin_centers = ilCDPermWrapper::getAdminCenters();

        $this->readCompany();

        $this->ot_id = (int) $_GET["otid"];
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        global $ilCtrl, $tpl, $ilUser;

        $tpl->setTitle($this->pl->txt("companies"));
        $tpl->setTitleIcon(ilUtil::getImagePath("icon_webr.svg"));

        $next_class = $ilCtrl->getNextClass();

        switch ($next_class) {
            case "ilcdcourseassignmentgui":
                $ilCtrl->setReturn($this, "listUsers");
                $cass = new ilCDCourseAssignmentGUI(false);
                $ilCtrl->forwardCommand($cass);
                return;
                break;
        }

        // personal development employee
        if (ilCDPermWrapper::isPerRole()) {
            // make sure, the user only acts on it's own company (or child companies)
            $child_companies = cdCompany::getChildCompanies($ilUser->getCompanyId());
            if ($_GET["company_id"] != $ilUser->getCompanyId() &&
                !isset($child_companies[$_GET["company_id"]])) {
                $_GET["company_id"] = $ilUser->getCompanyId();
            }
            
            $this->readCompany();
            $ilCtrl->setParameter($this, "company_id", $_GET["company_id"]);
            $cmd = $ilCtrl->getCmd("listUsers");
            if (!in_array($cmd, array("listCompanies", "addCompany", "confirmCompanyDeletion",
                "deleteCompany", "editCompany", "saveCompany", "updateCompany", "changeCompany"))) {
                $this->$cmd();
            }
        } elseif (count($this->user_admin_centers) > 0) {
            // cdc-ma or higher
            $cmd = $ilCtrl->getCmd("listCompanies");
            $this->$cmd();
        }
    }

    /**
     * Read company
     *
     * @param
     * @return
     */
    public function readCompany()
    {
        $this->company = new cdCompany((int) $_GET["company_id"]);
    }

    /**
     * List all companies
     *
     * @param
     * @return
     */
    public function listCompanies()
    {
        global $tpl, $ilToolbar, $ilCtrl, $lng;

        $ilToolbar->addButton(
            $this->pl->txt("add_company"),
            $ilCtrl->getLinkTarget($this, "addCompany")
        );

        $this->pl->includeClass("class.cdCompanyTableGUI.php");
        $table = new cdCompanyTableGUI($this, "listCompanies", $this->pl, $this->user_admin_centers);
        $table->setFilterCommand("applyCompFilter");        // parent GUI class must implement this function
        $table->setResetCommand("resetCompFilter");

        $tpl->setContent($table->getHTML());
    }

    /**
     * Add company
     */
    public function addCompany()
    {
        global $tpl;

        $this->initCompanyForm("create");
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Edit company
     */
    public function editCompany()
    {
        global $tpl;

        $this->initCompanyForm("edit");
        $this->getCompanyValues();
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Init company form.
     *
     * @param        int        $a_mode        Edit Mode
     */
    public function initCompanyForm($a_mode = "edit")
    {
        global $lng, $ilCtrl, $ilUser;

        $this->form = new ilPropertyFormGUI();

        // center
        $this->pl->includeClass("class.cdCenter.php");
        $centers = cdCenter::getAllCenters();
        $options = array();
        foreach ($centers as $c) {
            if (in_array($c["id"], $this->user_admin_centers)) {
                $options[$c["id"]] = $c["title"];
            }
        }
        $si = new ilSelectInputGUI($this->pl->txt("center"), "center_id");
        $si->setOptions($options);
        $si->setRequired(true);
        $this->form->addItem($si);

        // title
        $ti = new ilTextInputGUI($this->pl->txt("company_title"), "title");
        $ti->setMaxLength(200);
        $ti->setRequired(true);
        $this->form->addItem($ti);
        
        // title amendment
        $ta = new ilTextAreaInputGUI($this->pl->txt("company_title_amendment"), "title_amendment");
        $this->form->addItem($ta);
        
        // parent company
        if ($a_mode == "edit") {
            $opts = array(0 => "");
            foreach (cdCompany::getPossibleParentCompaniesForCenter(
                $this->company->getId()
            ) as $pc) {
                $opts[$pc["id"]] = $pc["title"];
            }
            $si = new ilSelectInputGUI($this->pl->txt("parent_company"), "parent_company");
            $si->setOptions($opts);
            $this->form->addItem($si);
        }

        // company password
        $ti = new ilTextInputGUI($this->pl->txt("company_password"), "company_password");
        $ti->setMaxLength(20);
        $ti->setRequired(true);
        $this->form->addItem($ti);

        // street
        $ti = new ilTextInputGUI($this->pl->txt("street"), "street");
        $ti->setMaxLength(200);
        $ti->setRequired(true);
        $this->form->addItem($ti);

        // postal code
        $ti = new ilTextInputGUI($this->pl->txt("postal_code"), "postal_code");
        $ti->setMaxLength(20);
        $ti->setRequired(true);
        $this->form->addItem($ti);

        // city
        $ti = new ilTextInputGUI($this->pl->txt("city"), "city");
        $ti->setMaxLength(200);
        $ti->setRequired(true);
        $this->form->addItem($ti);

        // country
        $ti = new ilTextInputGUI($this->pl->txt("country"), "country");
        $ti->setMaxLength(200);
        $ti->setRequired(true);
        $this->form->addItem($ti);

        // po box
        $ti = new ilTextInputGUI($this->pl->txt("po_box"), "po_box");
        $ti->setMaxLength(100);
        $this->form->addItem($ti);
        
        // address amendment
        $ta = new ilTextAreaInputGUI($this->pl->txt("company_address_amendment"), "address_amendment");
        $this->form->addItem($ta);

        // fax
        $ti = new ilTextInputGUI($this->pl->txt("fax"), "fax");
        $ti->setMaxLength(100);
        $this->form->addItem($ti);

        // internet
        $ti = new ilTextInputGUI($this->pl->txt("internet"), "internet");
        $ti->setMaxLength(200);
        $this->form->addItem($ti);

        // contact firstname
        $ti = new ilTextInputGUI($this->pl->txt("contact_firstname"), "contact_firstname");
        $ti->setMaxLength(100);
        $ti->setRequired(true);
        $this->form->addItem($ti);
        

        // contact lastname
        $ti = new ilTextInputGUI($this->pl->txt("contact_lastname"), "contact_lastname");
        $ti->setMaxLength(100);
        $ti->setRequired(true);
        $this->form->addItem($ti);

        // contact tel
        $ti = new ilTextInputGUI($this->pl->txt("contact_tel"), "contact_tel");
        $ti->setMaxLength(100);
        $ti->setRequired(true);
        $this->form->addItem($ti);
        
        // creation user

        /*  raus Schleife für Admin Heller 01.04.2016
        if (self::isAdmin())
        {
                                        */

        $options = array(
                0 => " - "
                );
        $users = ilObjUser::getAllNonCompanyUsers(false);
        foreach ($users as $u) {
            if ($u["id"] != ANONYMOUS_USER_ID) {
                $options[$u["id"]] = $u["lastname"] . ", " . $u["firstname"];
            }
        }
        $si = new ilSelectInputGUI($this->pl->txt("creation_user"), "creation_user");
        $si->setOptions($options);
        $si->setValue($ilUser->getId());
        $this->form->addItem($si);

        // Schleife ENDE
        // }

        /*  raus Heller 01.04.2016

        else
        {
            // non-editable, if user is not an admin
            $ne = new ilNonEditableValueGUI($this->pl->txt("creation_user"), "cu");
            $cr_user_id = ($a_mode == "create")
                ? $ilUser->getId()
                : $this->company->getCreationUser();
            $cr_name = ilObjUser::_lookupName($cr_user_id);
            $ne->setValue($cr_name["lastname"].", ".$cr_name["firstname"]);
            $this->form->addItem($ne);
        }

        */
        // save and cancel commands
        if ($a_mode == "create") {
            $this->form->addCommandButton("saveCompany", $lng->txt("save"));
            $this->form->addCommandButton("listCompanies", $lng->txt("cancel"));
            $this->form->setTitle($this->pl->txt("add_company"));
        } else {
            $this->form->addCommandButton("updateCompany", $lng->txt("save"));
            $this->form->addCommandButton("listCompanies", $lng->txt("cancel"));
            $this->form->setTitle($this->pl->txt("edit_company"));
        }

        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    /**
     * Get current values for company from
     */
    public function getCompanyValues()
    {
        $values = array();

        $values["title"] = $this->company->getTitle();
        $values["center_id"] = $this->company->getCenterId();
        $values["street"] = $this->company->getStreet();
        $values["postal_code"] = $this->company->getPostalCode();
        $values["city"] = $this->company->getCity();
        $values["country"] = $this->company->getCountry();
        $values["po_box"] = $this->company->getPoBox();
        $values["fax"] = $this->company->getFax();
        $values["internet"] = $this->company->getInternet();
        $values["contact_firstname"] = $this->company->getContactFirstname();
        $values["contact_lastname"] = $this->company->getContactLastname();
        $values["contact_tel"] = $this->company->getContactTel();
        $values["company_password"] = $this->company->getCompanyPassword();
        $values["parent_company"] = $this->company->getParentCompany();
        $values["creation_user"] = $this->company->getCreationUser();
        $values["title_amendment"] = $this->company->getTitleAmendment();
        $values["address_amendment"] = $this->company->getAddressAmendment();

        $this->form->setValuesByArray($values);
    }

    /**
     * Save company form
     */
    public function saveCompany()
    {
        global $tpl, $lng, $ilCtrl, $ilUser;

        $this->initCompanyForm("create");
        if ($this->form->checkInput()) {
            if (!cdCompany::checkUniquePassword($_POST["company_password"])) {
                ilUtil::sendFailure($lng->txt("form_input_not_valid"));
                $pw = $this->form->getItemByPostVar("company_password");
                $pw->setAlert($this->pl->txt("password_already_exists"));
            } else {
                $company = new cdCompany();
                $company->setTitle($_POST["title"]);
                $company->setCenterId($_POST["center_id"]);
                $company->setStreet($_POST["street"]);
                $company->setPostalCode($_POST["postal_code"]);
                $company->setCity($_POST["city"]);
                $company->setCountry($_POST["country"]);
                $company->setPoBox($_POST["po_box"]);
                $company->setFax($_POST["fax"]);
                $company->setContactFirstname($_POST["contact_firstname"]);
                $company->setContactLastname($_POST["contact_lastname"]);
                $company->setContactTel($_POST["contact_tel"]);
                $company->setCompanyPassword($_POST["company_password"]);
                $company->setInternet($_POST["internet"]);
                $company->setParentCompany((int) $_POST["parent_company"]);
                
                // Alle können speicher Heller - 06.01.2017
                //if (self::isAdmin())
                //{
                $company->setCreationUser((int) $_POST["creation_user"]);
                // }
                // else
                // {
                // 	$company->setCreationUser($ilUser->getId());
                // }

                $company->setTitleAmendment($_POST["title_amendment"]);
                $company->setAddressAmendment($_POST["address_amendment"]);
                $company->create();

                // perform save

                ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
                $ilCtrl->redirect($this, "listCompanies");
            }
        }

        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHtml());
    }

    /**
     * Update company
     */
    public function updateCompany()
    {
        global $lng, $ilCtrl, $tpl, $ilUser;

        $this->initCompanyForm("edit");
        if ($this->form->checkInput()) {
            if (!cdCompany::checkUniquePassword(
                $_POST["company_password"],
                $this->company->getId()
            )) {
                ilUtil::sendFailure($lng->txt("form_input_not_valid"));
                $pw = $this->form->getItemByPostVar("company_password");
                $pw->setAlert($this->pl->txt("password_already_exists"));
            } else {
                // only allow update, if company is within admin companies of user
                if (in_array($this->company->getCenterId(), $this->user_admin_centers)) {
                    // perform update
                    $this->company->setTitle($_POST["title"]);
                    $this->company->setCenterId($_POST["center_id"]);
                    $this->company->setStreet($_POST["street"]);
                    $this->company->setPostalCode($_POST["postal_code"]);
                    $this->company->setCity($_POST["city"]);
                    $this->company->setCountry($_POST["country"]);
                    $this->company->setPoBox($_POST["po_box"]);
                    $this->company->setFax($_POST["fax"]);
                    $this->company->setContactFirstname($_POST["contact_firstname"]);
                    $this->company->setContactLastname($_POST["contact_lastname"]);
                    $this->company->setContactTel($_POST["contact_tel"]);
                    $this->company->setCompanyPassword($_POST["company_password"]);
                    $this->company->setInternet($_POST["internet"]);
                    $this->company->setParentCompany((int) $_POST["parent_company"]);
                    
                    // Alle können speichern Heller 06.01.2017
                    // if (self::isAdmin())
                    // {
                    $this->company->setCreationUser((int) $_POST["creation_user"]);
                    // }
                    
                    $this->company->setTitleAmendment($_POST["title_amendment"]);
                    $this->company->setAddressAmendment($_POST["address_amendment"]);
                    $this->company->update();
                    ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
                }
                $ilCtrl->redirect($this, "listCompanies");
            }
        }

        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHtml());
    }

    /**
     * Confirm company deletion
     */
    public function confirmCompanyDeletion()
    {
        global $ilCtrl, $tpl, $lng;

        if (!is_array($_POST["company_id"]) || count($_POST["company_id"]) == 0) {
            ilUtil::sendInfo($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listCompanies");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($this->pl->txt("sure_delete_company"));
            $cgui->setCancel($lng->txt("cancel"), "listCompanies");
            $cgui->setConfirm($lng->txt("delete"), "deleteCompany");

            foreach ($_POST["company_id"] as $i) {
                $cgui->addItem("company_id[]", $i, cdCompany::lookupTitle($i));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete company
     *
     * @param
     * @return
     */
    public function deleteCompany()
    {
        global $ilCtrl, $lng;

        if (is_array($_POST["company_id"])) {
            foreach ($_POST["company_id"] as $i) {
                ${entity_small} = new cdCompany($i);
                ${entity_small}->delete();
            }
        }
        ilUtil::sendSuccess("msg_obj_modified");
        $ilCtrl->redirect($this, "listCompanies");
    }

    /**
     * List users of company
     *
     * @param
     * @return
     */
    public function listUsers()
    {
        global $tpl, $ilTabs, $ilCtrl, $lng, $ilToolbar, $ilCtrl, $ilUser;

        $this->companyHeader();

        // child company selection
        if (ilCDPermWrapper::isPerRole()) {
            $child_companies = cdCompany::getChildCompanies($ilUser->getCompanyId());
            if ($child_companies > 0) {
                $opts = array($ilUser->getCompanyId() =>
                    cdCompany::lookupTitle($ilUser->getCompanyId()));
                foreach ($child_companies as $cc) {
                    $opts[$cc["id"]] = "- " . $cc["title"];
                }
                
                $si = new ilSelectInputGUI("", "company_id");
                $si->setOptions($opts);
                $ilToolbar->addInputItem($si);
                
                $ilToolbar->setFormAction($ilCtrl->getFormAction($this));
                
                $ilToolbar->addFormButton($lng->txt("select"), "selectCompany");
                
                $ilToolbar->addSeparator();
            }
        }
        
        if (ilCDPermWrapper::checkUserCreationPossible($this->company->getId())) {
            $ilCtrl->setParameter($this, "user_id", "");
            $ilToolbar->addButton(
                $this->pl->txt("add_user"),
                $ilCtrl->getLinkTarget($this, "addCompanyUser")
            );
        } else {
            ilUtil::sendInfo($this->pl->txt("not_possible_to_create_user"));
        }
        $ilToolbar->addButton(
            $this->pl->txt("export_excel"),
            $ilCtrl->getLinkTarget($this, "exportExcel")
        );

        // cdc-ma or higher: back button to list of companies
        if (count($this->user_admin_centers) > 0) {
            $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, ""));
        }
        
        $this->pl->includeClass("class.ilCompanyUserTableGUI.php");

        $table = new ilCompanyUserTableGUI(
            $this,
            "listUsers",
            $this->pl,
            $this->user_admin_centers,
            $this->company
        );
        $tpl->setContent($table->getHTML());
    }

    /**
     * Select company
     *
     * @param
     * @return
     */
    public function selectCompany()
    {
        global $ilCtrl;
        
        $ilCtrl->setParameter($this, "company_id", (int) $_POST["company_id"]);
        $ilCtrl->redirect($this, "listUsers");
    }
    
    
    /**
     * Company Header
     *
     * @param
     * @return
     */
    public function companyHeader()
    {
        global $tpl;

        if (count($this->user_admin_centers) > 0) {
            $tpl->setTitle($this->pl->txt("company") . ": " .
                cdCompany::lookupTitle((int) $_GET["company_id"]));
        } else {
            $tpl->setTitle($this->pl->txt("participants") . ": " .
                cdCompany::lookupTitle((int) $_GET["company_id"]));
        }
    }

    ////
    //// Company user
    ////

    /**
     * Get company user profile
     *
     * @param
     * @return
     */
    public function getCompanyUserProfile()
    {
        $up = new ilUserProfile();
        $up->skipField("company_password");
        $up->skipGroup("settings");
        $up->skipGroup("preferences");
        $up->skipField("roles");
        return $up;
    }

    /**
     * Add Company User
     *
     * @param
     * @return
     */
    public function addCompanyUser()
    {
        global $tpl;

        $this->initCompanyUserForm("create");
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Company User
     *
     * @param
     * @return
     */
    public function editCompanyUser()
    {
        global $tpl;

        $this->initCompanyUserForm();
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Init company user form.
     *
     * @param        int        $a_mode        Edit Mode
     */
    public function initCompanyUserForm($a_mode = "edit")
    {
        global $lng, $ilCtrl;

        $this->companyHeader();

        $this->form = new ilPropertyFormGUI();

        $user = new ilObjUser((int) $this->user_id);

        $up = $this->getCompanyUserProfile();

        // standard fields
        if ($a_mode == "create") {
            $up->setMode(ilUserProfile::MODE_REGISTRATION);
        }
        $up->addStandardFieldsToForm($this->form, $user);

        // save and cancel commands
        if ($a_mode == "create") {
            $this->form->addCommandButton("saveCompanyUser", $lng->txt("save"));
            $this->form->addCommandButton("listUsers", $lng->txt("cancel"));
        } else {
            $this->form->setTitle($lng->txt("usr_edit"));
            $this->form->addCommandButton("updateCompanyUser", $lng->txt("save"));
            $this->form->addCommandButton("listUsers", $lng->txt("cancel"));
        }

        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    /**
     * Save company user
     *
     * @param
     * @return
     */
    public function saveCompanyUser()
    {
        global $ilCtrl, $lng, $tpl, $ilSetting;

        $this->initCompanyUserForm("create");
        if ($this->form->checkInput()) {

            // custom validation

            $form_valid = true;

            // validate username
            $login_obj = $this->form->getItemByPostVar('username');
            $login = $this->form->getInput("username");
            if (!ilUtil::isLogin($login)) {
                $login_obj->setAlert($lng->txt("login_invalid"));
                $form_valid = false;
            } elseif (ilObjUser::_loginExists($login)) {
                $login_obj->setAlert($lng->txt("login_exists"));
                $form_valid = false;
            } elseif ((int) $ilSetting->get('allow_change_loginname') &&
                (int) $ilSetting->get('prevent_reuse_of_loginnames') &&
                ilObjUser::_doesLoginnameExistInHistory($login)) {
                $login_obj->setAlert($lng->txt('login_exists'));
                $form_valid = false;
            }

            if (!$form_valid) {
                ilUtil::sendFailure($lng->txt('form_input_not_valid'));
            } else {
                $this->__createUser();
                
                ilUtil::sendSuccess($this->pl->txt("added_user"), 1);
                $ilCtrl->redirect($this, "listUsers");
            }
        }

        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Create company user
     *
     * @param
     * @return
     */
    public function __createUser()
    {
        global $ilSetting, $rbacadmin;

        $this->userObj = new ilObjUser();

        $up = new ilUserProfile();
        $up->setMode(ilUserProfile::MODE_REGISTRATION);
        $up->setUserFromFields($this->form, $this->userObj);

        $this->userObj->setTitle($this->userObj->getFullname());
        $this->userObj->setDescription($this->userObj->getEmail());

        $password = $this->form->getInput("usr_password");
        $this->userObj->setPasswd($password);

        $password_addon = $this->form->getInput("usr_password_addon");
        $this->userObj->setPasswordAddon($password_addon);

        $this->pl->includeClass("class.cdCompany.php");
        $company_id = (int) $_GET["company_id"];
        $this->userObj->setCompanyId($company_id);

        // Set user defined data
        $user_defined_fields = ilUserDefinedFields::_getInstance();
        $defs = $user_defined_fields->getRegistrationDefinitions();
        $udf = array();
        foreach ($_POST as $k => $v) {
            if (substr($k, 0, 4) == "udf_") {
                $f = substr($k, 4);
                $udf[$f] = $v;
            }
        }
        $this->userObj->setUserDefinedData($udf);

        $this->userObj->setTimeLimitOwner(7);

        $default_role = false;
        $this->userObj->setTimeLimitUnlimited(1);
        $this->userObj->setTimeLimitUntil(time());

        $this->userObj->setTimeLimitFrom(time());

        $this->userObj->create();

        $this->userObj->setActive(1);

        $this->userObj->updateOwner();

        // set a timestamp for last_password_change
        // this ts is needed by the ACCOUNT_SECURITY_MODE_CUSTOMIZED
        // in ilSecuritySettings
        $this->userObj->setLastPasswordChangeTS(time());

        //insert user data in table user_data
        $this->userObj->saveAsNew();

        // setup user preferences
        $this->userObj->setLanguage($this->form->getInput('usr_language'));
        $hits_per_page = $ilSetting->get("hits_per_page");
        if ($hits_per_page < 10) {
            $hits_per_page = 10;
        }
        $this->userObj->setPref("hits_per_page", $hits_per_page);
        $show_online = $ilSetting->get("show_users_online");
        if ($show_online == "") {
            $show_online = "y";
        }
        $this->userObj->setPref("show_users_online", $show_online);
        $this->userObj->writePrefs();

        // cdpatch start
        //		$rbacadmin->assignUser((int)$default_role, $this->userObj->getId(), true);
        ilCDPermWrapper::initCompanyUserRoles($this->userObj->getId(), $company_id);
        // cdpatch end
    }

    /**
     * Update company user
     */
    public function updateCompanyUser()
    {
        global $lng, $ilCtrl, $tpl;

        $this->initCompanyUserForm("edit");
        if ($this->form->checkInput()) {

            // perform update
            $user = new ilObjUser((int) $this->user_id);
            $up = $this->getCompanyUserProfile();
            $up->skipField("username");
            $up->skipField("password");
            foreach ($up->getStandardFields() as $f => $d) {
                $m = "set" . substr($d["method"], 3);
                $user->$m($_POST["usr_" . $f]);
            }

            $pw = $_POST["usr_password"];
            if ($pw != "********" and strlen($pw)) {
                $user->setPasswd($pw, IL_PASSWD_PLAIN);
            }

            $user->update();
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "editCompanyUser");
        }

        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHtml());
    }

    /**
     * Show learn data of user(s)
     *
     * @param
     * @return
     */
    public function showLearnData()
    {
        global $tpl, $ilTabs, $lng, $ilCtrl;

        $this->companyHeader();

        if (count($this->user_admin_centers) == 0 && !ilCDPermWrapper::isPerRole(true)) {
            return;
        }

        $n = ilObjUser::_lookupName($this->user_id);

        $tpl->setTitle($this->pl->txt("learn_data") . ": " . $n["lastname"] .
            ", " . $n["firstname"]);
        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "listUsers")
        );

        // needs analysis
        $this->pl->includeClass("class.cdNeedsAnalysisFactory.php");
        $na_gui = cdNeedsAnalysisFactory::getGUIInstance($this->pl);
        $html = $na_gui->getPresentationView($this->user_id);

        // self evaluation
        $na_gui = new ilSkillSelfEvaluationGUI();
        $html .= $na_gui->getPresentationView($this->user_id);

        // tests
        $this->pl->includeClass("class.cdEntryLevelTestGUI.php");
        $t_gui = new cdEntryLevelTestGUI($this->pl);
        $html .= $t_gui->getPresentationView($this->user_id);

        $tpl->setContent($html);
    }

    /**
     * Confirm company user deletion
     */
    public function confirmCompanyUserDeletion()
    {
        global $ilCtrl, $tpl, $lng;

        $this->companyHeader();

        if (!is_array($_POST["id"]) || count($_POST["id"]) == 0) {
            ilUtil::sendInfo($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listUsers");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($this->pl->txt("really_delete_users"));
            $cgui->setCancel($lng->txt("cancel"), "listUsers");
            $cgui->setConfirm($lng->txt("delete"), "deleteCompanyUsers");

            foreach ($_POST["id"] as $i) {
                $name = ilObjUser::_lookupName($i);
                $cgui->addItem("id[]", $i, $name["lastname"] . ", " .
                    $name["firstname"] . " [" . $name["login"] . "]");
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete company users
     *
     * @param
     * @return
     */
    public function deleteCompanyUsers()
    {
        global $ilCtrl;
        
        foreach ($_POST["id"] as $i) {
            // check if id belongs to a user
            if (ilObject::_lookupType($i) == "usr") {
                $user = new ilObjUser($i);
                $user->delete();
            }
            ilUtil::sendSuccess($this->pl->txt("users_have_been_deleted"));
        }

        $ilCtrl->redirect($this, "listUsers");
    }


    /**
    * Apply filter
    */
    public function applyFilter()
    {
        $this->pl->includeClass("class.ilCompanyUserTableGUI.php");
        $table = new ilCompanyUserTableGUI(
            $this,
            "listUsers",
            $this->pl,
            $this->user_admin_centers,
            $this->company
        );
        $table->writeFilterToSession();        // writes filter to session
        $table->resetOffset();                // sets record offest to 0 (first page)
        $this->listUsers();
    }

    /**
    * Reset filter
    */
    public function resetFilter()
    {
        $this->pl->includeClass("class.ilCompanyUserTableGUI.php");
        $table = new ilCompanyUserTableGUI(
            $this,
            "listUsers",
            $this->pl,
            $this->user_admin_centers,
            $this->company
        );
        $table->resetOffset();                // sets record offest to 0 (first page)
        $table->resetFilter();                // clears filter
        $this->listUsers();
    }
    
    /**
    * Apply filter
    */
    public function applyCompFilter()
    {
        $this->pl->includeClass("class.cdCompanyTableGUI.php");
        $table = new cdCompanyTableGUI($this, "listCompanies", $this->pl, $this->user_admin_centers);
        $table->writeFilterToSession();        // writes filter to session
        $table->resetOffset();                // sets record offest to 0 (first page)
        $this->listCompanies();
    }

    /**
    * Reset filter
    */
    public function resetCompFilter()
    {
        $this->pl->includeClass("class.cdCompanyTableGUI.php");
        $table = new cdCompanyTableGUI($this, "listCompanies", $this->pl, $this->user_admin_centers);
        $table->resetOffset();                // sets record offest to 0 (first page)
        $table->resetFilter();                // clears filter
        $this->listCompanies();
    }

    ////
    //// Oral/target stuff
    ////


    /**
     * Edit oral/target of a user
     *
     * @param
     * @return
     */
    public function showOralTarget()
    {
        global $tpl, $ilTabs, $lng, $ilCtrl, $ilToolbar;
        
        $this->companyHeader();

        if (count($this->user_admin_centers) == 0 && !ilCDPermWrapper::isPerRole(true)) {
            return;
        }

        //
        $this->pl->includeClass("class.cdLang.php");
        $si = new ilSelectInputGUI($lng->txt("language"), "lang");
        $si->setOptions(cdLang::getLanguagesAsOptions("", true));
        $ilToolbar->addInputItem($si, true);
        $ilToolbar->setFormAction($ilCtrl->getFormAction($this));
        $ilToolbar->addFormButton($this->pl->txt("add_ot_entry"), "addOralTarget");
        
        $ilCtrl->saveParameter($this, "user_id");
        $n = ilObjUser::_lookupName($this->user_id);

        $tpl->setTitle($this->pl->txt("oral_target") . ": " . $n["lastname"] .
            ", " . $n["firstname"]);
        $ilTabs->setBackTarget(
            $lng->txt("back"),
            $ilCtrl->getLinkTarget($this, "listUsers")
        );

        $this->pl->includeClass("class.cdOralTargetTableGUI.php");
        $tab = new cdOralTargetTableGUI(
            $this,
            "showOralTarget",
            $this->pl,
            (int) $this->user_id
        );
        
        $tpl->setContent($tab->getHTML());
    }
    
    /**
     * Save oral target marks
     *
     * @param
     * @return
     */
    /*	function saveOralTarget()
        {
            global $ilCtrl, $lng;

            $ilCtrl->saveParameter($this, "user_id");

            $this->pl->includeClass("class.cdNeedsAnalysis.php");
            $this->pl->includeClass("class.cdOralTarget.php");
            foreach (cdNeedsAnalysis::getLanguages() as $l)
            {
                cdOralTarget::write((int) $this->user_id, $l, $_POST["oral"][$l], $_POST["target"][$l],
                    $_POST["course_type"][$l]);
            }
    //	var_dump($_POST); exit;
            ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "editOralTarget");
        }*/

    /**
     * Edit oral target
     */
    public function editOralTarget()
    {
        global $tpl;

        $form = $this->initOralTargetForm("edit");
        $tpl->setContent($form->getHTML());
    }

    /**
     * Add oral target
     */
    public function addOralTarget()
    {
        global $tpl;

        $form = $this->initOralTargetForm("add");
        $tpl->setContent($form->getHTML());
    }

    /**
     * Init oral target form.
     */
    public function initOralTargetForm($a_mode = "edit")
    {
        global $lng, $ilCtrl;

        $lng->loadLanguageModule("meta");

        $lang_levels = array("" => "");
        $this->pl->includeClass("class.cdLang.php");
        foreach (cdLang::getLangLevels() as $l) {
            $lang_levels[$l] = $l;
        }

        $lang = $_POST["lang"];

        $form = new ilPropertyFormGUI();

        // language
        $ne = new ilNonEditableValueGUI($lng->txt("language"), "");
        $form->addItem($ne);

        // date
        $dt = new ilDateTimeInputGUI($lng->txt("date"), "date");
        $dt->setShowTime(false);
        //$dt->setMode(ilDateTimeInputGUI::MODE_SELECT);
        $form->addItem($dt);

        // oral
        $or = new ilSelectInputGUI($this->pl->txt("oral"), "oral");
        $or->setOptions($lang_levels);
        $form->addItem($or);
        
        // target
        $ta = new ilSelectInputGUI($this->pl->txt("target"), "target");
        $ta->setOptions($lang_levels);
        $form->addItem($ta);
        
        // course type
        $this->pl->includeClass("class.cdCourseType.php");
        $options = [];
        foreach (cdCourseType::getAllCourseTypes($lang) as $ct) {
            $options[$ct["id"]] = $ct["title"];
        }

        $ct = new ilSelectInputGUI($this->pl->txt("course_type"), "course_type");
        $ct->setOptions($options);
        $form->addItem($ct);

        if ($a_mode == "edit") {
            $this->pl->includeClass("class.cdOralTarget.php");
            $ot = new cdOralTarget($this->ot_id);
            $lang = $ot->getLanguage();
            $or->setValue($ot->getOral());
            $ta->setValue($ot->getTarget());
            $ct->setValue($ot->getCourseType());
            $dt->setDate(new ilDate($ot->getDate(), IL_CAL_DATE));
        } else {
            // language
            $hi = new ilHiddenInputGUI("lang");
            $hi->setValue($lang);
            $form->addItem($hi);
        }

        $ne->setValue($lng->txt("meta_l_" . $lang));

        // save and cancel commands
        if ($a_mode == "add") {
            $form->addCommandButton("saveOralTarget", $lng->txt("save"));
            $form->addCommandButton("showOralTarget", $lng->txt("cancel"));
        } else {
            $form->addCommandButton("updateOralTarget", $lng->txt("save"));
            $form->addCommandButton("showOralTarget", $lng->txt("cancel"));
        }

        $form->setTitle($this->pl->txt("oral_target"));
        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }

    /**
     * Save oral target
     */
    public function saveOralTarget()
    {
        global $tpl, $lng, $ilCtrl;

        $form = $this->initOralTargetForm();
        if ($form->checkInput()) {
            $ot = new cdOralTarget();
            $ot->setDate($form->getItemByPostVar("date")->getDate()->get(IL_CAL_DATE));
            $ot->setOral($form->getInput("oral"));
            $ot->setTarget($form->getInput("target"));
            $ot->setCourseType((int) $form->getInput("course_type"));
            $ot->setUserId($this->user_id);
            $ot->setLanguage($form->getInput("lang"));
            $ot->save();
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "showOralTarget");
        } else {
            $this->form->setValuesByPost();
            $tpl->setContent($form->getHtml());
        }
    }

    /**
     * Save oral target
     */
    public function updateOralTarget()
    {
        global $tpl, $lng, $ilCtrl;

        $form = $this->initOralTargetForm();
        if ($form->checkInput()) {
            $ot = new cdOralTarget($this->ot_id);
            $ot->setDate($form->getItemByPostVar("date")->getDate()->get(IL_CAL_DATE));
            $ot->setOral($form->getInput("oral"));
            $ot->setTarget($form->getInput("target"));
            $ot->setCourseType((int) $form->getInput("course_type"));
            $ot->update();
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "showOralTarget");
        } else {
            $this->form->setValuesByPost();
            $tpl->setContent($form->getHtml());
        }
    }

    /**
     * Confirm oral target deletion
     */
    public function confirmOralTargetDelete()
    {
        global $ilCtrl, $tpl, $lng;

        $this->pl->includeClass("class.cdOralTarget.php");
        $lng->loadLanguageModule("meta");

        if (!is_array($_POST["otid"]) || count($_POST["otid"]) == 0) {
            ilUtil::sendInfo($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "showOralTarget");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($this->pl->txt("sure_delete_ot"));
            $cgui->setCancel($lng->txt("cancel"), "showOralTarget");
            $cgui->setConfirm($lng->txt("delete"), "deleteOralTarget");

            foreach ($_POST["otid"] as $i) {
                $ot = new cdOralTarget($i);
                $cgui->addItem("otid[]", $i, $ot->getDate() . ", " . $lng->txt("meta_l_" . $ot->getLanguage()) . ", " . $ot->getOral() . ", " . $ot->getTarget());
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Delete oral target
     *
     * @param
     * @return
     */
    public function deleteOralTarget()
    {
        global $ilCtrl, $lng;

        $this->pl->includeClass("class.cdOralTarget.php");
        if (is_array($_POST["otid"])) {
            foreach ($_POST["otid"] as $otid) {
                $ot = new cdOralTarget($otid);
                $ot->delete();
            }
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        }
        $ilCtrl->redirect($this, "showOralTarget");
    }


    ////
    //// Export
    ////

    /**
     * Export excel (selected)
     *
     * @param
     * @return
     */
    public function exportExcelSelected()
    {
        global $ilCtrl;

        if (is_array($_POST["id"]) && count($_POST["id"]) > 0) {
            $this->company->exportExcel($this->pl, $_POST["id"]);
        } else {
            $ilCtrl->redirect($this, "listUsers");
        }
    }

    /**
     * Export learning data as excel
     */
    public function exportExcel()
    {
        $this->company->exportExcel($this->pl);
    }
    
    /**
     * Check whether current user is an admin
     */
    public static function isAdmin()
    {
        return ilCDPermWrapper::isAdmin();
    }

    ////
    //// Change company
    ////

    /**
     * Confirm changing company of users
     */
    public function confirmChangeCompany()
    {
        global $ilCtrl, $tpl, $lng;

        if (!is_array($_POST["id"]) || count($_POST["id"]) == 0) {
            ilUtil::sendInfo($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listUsers");
        } else {
            $form = new ilPropertyFormGUI();
            $form->addCommandButton("changeCompany", $this->pl->txt("change_company"));
            $form->addCommandButton("listUsers", $lng->txt("cancel"));
            $form->setTitle($this->pl->txt("change_company"));
            $form->setFormAction($ilCtrl->getFormAction($this));

            // select company
            $this->pl->includeClass("class.cdCenter.php");
            $comp = cdCompany::getAllCompanies(true, $this->user_admin_centers, array());
            $options = array();
            $center_name = array();
            foreach ($comp as $k => $c) {
                if (!isset($center_name[$c["center_id"]])) {
                    $center_name[$c["center_id"]] =
                    cdCenter::lookupTitle($c["center_id"]);
                }
                $options[$c["id"]] = $c["title"] . " (" . $center_name[$c["center_id"]] . ")";
            }

            $si = new ilSelectInputGUI($this->pl->txt("new_company"), "new_company");
            $si->setOptions($options);
            $si->setValue((int) $_GET["company_id"]);
            $form->addItem($si);

            // users
            $hi = new ilHiddenInputGUI("user_ids");
            $hi->setValue(implode(";", $_POST["id"]));
            $form->addItem($hi);


            // confirmation gui (list only)
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($this->pl->txt("sure_to_change_company"));

            foreach ($_POST["id"] as $i) {
                $cgui->addItem("id[]", $i, ilUserUtil::getNamePresentation($i, false, false, "", true));
            }
            
            $tpl->setContent($form->getHTML() . "<br/>" . $cgui->getHTML());
        }
    }

    /**
     * Change company
     */
    public function changeCompany()
    {
        global $ilCtrl, $lng;

        $user_ids = explode(";", $_POST["user_ids"]);
        foreach ($user_ids as $user_id) {
            ilCDUserUtil::setUserCompany((int) $user_id, (int) $_POST["new_company"]);
        }

        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "listUsers");
    }


    ////
    //// Participant evaluations
    ////

    /**
     * Show participant evaluation overview
     */
    public function showPartEvalOverview()
    {
        global $tpl, $ilTabs, $ilCtrl, $lng;

        $this->companyHeader();
        $ilTabs->setBackTarget($lng->txt("back"), $ilCtrl->getLinkTarget($this, "listUsers"));

        $this->pl->includeClass("class.cdPartEvalOverviewTableGUI.php");
        $tab = new cdPartEvalOverviewTableGUI($this, "showPartEvalOverview", $this->pl, $this->user_id);

        $tpl->setContent($tab->getHTML());
    }

    /**
     * Confirm changing company of users
     */
    public function confirmPublishPartEval()
    {
        global $ilCtrl, $tpl, $lng;

        if (!is_array($_POST["peid"]) || count($_POST["peid"]) == 0) {
            ilUtil::sendInfo($lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "showPartEvalOverview");
        } else {
            // confirmation gui (list only)
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($this->pl->txt("sure_publish_part_evals"));
            $cgui->setCancel($lng->txt("cancel"), "showPartEvalOverview");
            $cgui->setConfirm($this->pl->txt("publish"), "publishPartEval");

            foreach ($_POST["peid"] as $i) {
                $cgui->addItem("peid[]", $i, $i);
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Publish participant evaluatoin
     */
    public function publishPartEval()
    {
        global $lng, $ilCtrl;

        $this->pl->includeClass("class.cdParticipantEvaluation.php");
        if (is_array($_POST["peid"])) {
            foreach ($_POST["peid"] as $peid) {
                $pe = new cdParticipantEvaluation($peid);
                $pe->setPublished(true);
                $pe->update();
            }
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        }
        $ilCtrl->redirect($this, "showPartEvalOverview");
    }

    /**
     * Publish participant evaluatoin
     */
    public function unpublishPartEval()
    {
        global $lng, $ilCtrl;

        $this->pl->includeClass("class.cdParticipantEvaluation.php");
        if (is_array($_POST["peid"])) {
            foreach ($_POST["peid"] as $peid) {
                $pe = new cdParticipantEvaluation($peid);
                $pe->setPublished(false);
                $pe->update();
            }
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        }
        $ilCtrl->redirect($this, "showPartEvalOverview");
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
