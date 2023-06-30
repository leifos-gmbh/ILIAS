<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/User/classes/class.ilUserProfile.php");

/**
 * Class ilUserProfile
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesUser
 */
class ilCDTrainerProfile extends ilUserProfile
{
    // see also cdNeedsAnalysis
    public $tech_lang = array();
    
    public $teach_types = array();


    // this array should be used in all places where user data is tackled
    // in the future: registration, personal profile, user administration
    // public profile, user import/export
    // for now this is not implemented yet. Please list places, that already use it:
    //
    // - personal profile
    // - (global) standard user profile fields settings
    //
    // the following attributes are defined (can be extended if needed):
    // - input: input type
    //			standard inputs: text, radio, selection, textarea
    //			special inputs: login
    // - input dependend attributes
    //		- maxlength, sizte for text
    //		- values array for radio
    //		- cols/rows for text areas
    //		- options array for selections
    // - method: ilObjUser get-method, e.g. getFirstname
    // - group: group id (id is also used as lang_var for sub headers in forms
    // - lang_var: if key should not be used as lang var, this overwrites the usage in forms
    // - settings property related attributes, settingsproperties are ("visible", "changeable",
    //   "searchable", "required", "export", "course_export" and "registration")
    // 		- <settingsproperty>_hide: hide this property in settings (not implemented)
    // 		- <settingsproperty>_fix_value: property has a fix value (cannot be changed)
    // cdpatch start (only new fields)
    private static $user_field = array(
        "username" => array(
                        "input" => "login",
                        "maxlength" => 32,
                        "size" => 40,
                        "method" => "getLogin",
                        "course_export_fix_value" => 1,
                        "group_export_fix_value" => 1,
                        "changeable_hide" => true,
                        "required_hide" => true,
                        "table" => "usr_data",
                        "group" => "personal_data"),
        "center_id" => array(
                        "input" => "hidden",
                        "group" => "personal_data",
                        "table" => "cd_trainer",
                        "type" => "integer"),
        "password" => array(
                        "input" => "password",
                        "required_hide" => true,
                        "visib_reg_hide" => true,
                        'visib_lua_fix_value' => 0,
                        "course_export_hide" => true,
                        "group_export_hide" => true,
                        "lists_hide" => true,
                        "group" => "personal_data"),
        "gender" => array(
                        "input" => "radio",
                        "values" => array("f" => "gender_f", "m" => "gender_m"),
                        "method" => "getGender",
                        "table" => "usr_data",
                        "group" => "personal_data"),
        "title" => array(
                        "input" => "text",
                        "lang_var" => "person_title",
                        "maxlength" => 32,
                        "size" => 40,
                        "method" => "getUTitle",
                        "table" => "usr_data",
                        "group" => "personal_data"),
        "firstname" => array(
                        "input" => "text",
                        "maxlength" => 32,
                        "size" => 40,
                        "method" => "getFirstname",
                        "required_fix_value" => 1,
                        "visib_reg_fix_value" => 1,
                        'visib_lua_fix_value' => 1,
                        "course_export_fix_value" => 1,
                        "group_export_fix_value" => 1,
                        "table" => "usr_data",
                        "group" => "personal_data"),
        "lastname" => array(
                        "input" => "text",
                        "maxlength" => 32,
                        "size" => 40,
                        "method" => "getLastname",
                        "required_fix_value" => 1,
                        "visib_reg_fix_value" => 1,
                        'visib_lua_fix_value' => 1,
                        "course_export_fix_value" => 1,
                        "group_export_fix_value" => 1,
                        "table" => "usr_data",
                        "group" => "personal_data"),
        "birthday" => array(
                        "input" => "birthday",
                        "lang_var" => "birthday",
                        "maxlength" => 32,
                        "size" => 40,
                        "required_fix_value" => 1,
                        "method" => "getBirthday",
                        "table" => "usr_data",
                        "group" => "personal_data"),
        "cd_sel_country" => array(
                        "input" => "cd_sel_country",
                        "required_fix_value" => 1,
                        "method" => "getSelectedCountry",
                        "table" => "usr_data",
                        "group" => "personal_data"),
        "cd_sel_country2" => array(
                        "input" => "cd_sel_country",
                        "required_fix_value" => 0,
                        "method" => "getSelectedCountry2",
                        "table" => "cd_trainer",
                        "group" => "personal_data",
                        "type" => "text"),
        "street" => array(
                        "input" => "text",
                        "maxlength" => 40,
                        "size" => 40,
                        "required_fix_value" => 1,
                        "method" => "getStreet",
                        "table" => "usr_data",
                        "group" => "contact_data"),
        "zipcode" => array(
                        "input" => "text",
                        "maxlength" => 10,
                        "size" => 10,
                        "required_fix_value" => 1,
                        "method" => "getZipcode",
                        "table" => "usr_data",
                        "group" => "contact_data"),
        "city" => array(
                        "input" => "text",
                        "maxlength" => 40,
                        "size" => 40,
                        "required_fix_value" => 1,
                        "method" => "getCity",
                        "table" => "usr_data",
                        "group" => "contact_data"),
        "country" => array(
                        "input" => "text",
                        "maxlength" => 40,
                        "size" => 40,
                        "required_fix_value" => 1,
                        "method" => "getCountry",
                        "table" => "usr_data",
                        "group" => "contact_data"),
        "phone_office" => array(
                        "input" => "text",
                        "maxlength" => 40,
                        "size" => 40,
                        "required_fix_value" => 1,
                        "method" => "getPhoneOffice",
                        "table" => "usr_data",
                        "group" => "contact_data"),
        "phone_mobile" => array(
                        "input" => "text",
                        "maxlength" => 40,
                        "size" => 40,
                        "method" => "getPhoneMobile",
                        "table" => "usr_data",
                        "group" => "contact_data"),
        "fax" => array(
                        "input" => "text",
                        "maxlength" => 40,
                        "size" => 40,
                        "method" => "getFax",
                        "table" => "usr_data",
                        "group" => "contact_data"),
        "email" => array(
                        "input" => "email",
                        "maxlength" => 40,
                        "size" => 40,
                        "method" => "getEmail",
                        "table" => "usr_data",
                        "group" => "contact_data"),
        "residence_permit" => array(
                        "input" => "checkbox",
                        "method" => "getResidencePermit",
                        "group" => "permit_license",
                        "table" => "cd_trainer",
                        "type" => "integer"),
        "work_permit" => array(
                        "input" => "checkbox",
                        "method" => "getWorkPermit",
                        "group" => "permit_license",
                        "table" => "cd_trainer",
                        "type" => "integer"),
        "driving_license" => array(
                        "input" => "checkbox",
                        "method" => "getDrivingLicense",
                        "group" => "permit_license",
                        "table" => "cd_trainer",
                        "type" => "integer"),
        "car_owner" => array(
                        "input" => "checkbox",
                        "method" => "getCarOwner",
                        "group" => "permit_license",
                        "table" => "cd_trainer",
                        "type" => "integer"),
        "mother_tongue" => array(
                        "input" => "multi_language",
                        "method" => "getMotherTongues",
                        "group" => "languages",
                        "table" => "cd_trainer_mtl"),
        "instruction_language" => array(
                        "input" => "multi_language",
                        "method" => "getInstructionLanguages",
                        "group" => "languages",
                        "table" => "cd_trainer_il"),
        "language_skills" => array(
                        "input" => "multi_language_skill",
                        "method" => "getLanguageSkills",
                        "group" => "languages",
                        "table" => "cd_trainer_ls"),
        "work_exp_lang_train" => array(
                        "input" => "textarea",
                        "required_fix_value" => 1,
                        "info" => "work_experience_lang_train_desc",
                        "method" => "getWorkExperience",
                        "group" => "work_experience",
                        "table" => "cd_trainer",
                        "type" => "text"),
        "given_lessons" => array(
                        "input" => "lesson_number",
                        "method" => "getGivenLessons",
                        "group" => "work_experience",
                        "table" => "cd_trainer",
                        "type" => "integer"),
        "work_experience_other" => array(
                        "input" => "textarea",
                        "required_fix_value" => 1,
                        "method" => "getWorkExperienceOther",
                        "group" => "work_experience",
                        "table" => "cd_trainer",
                        "type" => "text"),
        "further_education" => array(
                        "input" => "further_education",
                        "method" => "getFurtherEducations",
                        "group" => "further_education",
                        "table" => "cd_trainer_fe"),
        "general_education" => array(
                        "input" => "education",
                        "method" => "getGeneralEducation",
                        "group" => "education",
                        "table" => "cd_trainer",
                        "type" => "text"),
        "general_education_other" => array(
                        "input" => "text",
                        "method" => "getGeneralEducationOther",
                        "group" => "education",
                        "table" => "cd_trainer",
                        "type" => "text"),
        "lang_education" => array(
                        "input" => "lang_education",
                        "method" => "getLanguageEducation",
                        "group" => "education",
                        "table" => "cd_trainer",
                        "type" => "text"),
        "lang_education_other" => array(
                        "input" => "text",
                        "method" => "getLanguageEducationOther",
                        "group" => "education",
                        "table" => "cd_trainer",
                        "type" => "text"),
        "teaching_experience" => array(
                        "input" => "textarea",
                        "info" => "teaching_experience_desc",
                        "method" => "getTeachingExperience",
                        "group" => "competences",
                        "table" => "cd_trainer",
                        "type" => "text"),
        "teach_exp_tech_lang" => array(
                        "input" => "tech_lang_exp",
                        "method" => "getTeachExpTechLang",
                        "group" => "competences",
                        "table" => "cd_trainer_tl"),
        "other_tech_lang" => array(
                        "input" => "textarea",
                        "method" => "getOtherTechLang",
                        "group" => "competences",
                        "table" => "cd_trainer",
                        "type" => "text"),
        "teach_type_exp" => array(
                        "input" => "teach_type_exp",
                        "method" => "getTeachTypeExperience",
                        "group" => "competences",
                        "table" => "cd_trainer_tt"),
        "cefr_knowledge" => array(
                        "input" => "cefr_knowledge",
                        "method" => "getCefrKnowledge",
                        "group" => "cefr",
                        "table" => "cd_trainer",
                        "type" => "integer"),
        "favorite_cefr_levels" => array(
                        "input" => "cefr_levels",
                        "method" => "getFavoriteCefrLevels",
                        "group" => "cefr",
                        "table" => "cd_trainer",
                        "type" => "text",
                        "multi_value" => true),
        "cefr_exam_prep_license" => array(
                        "input" => "cefr_prep_licenses",
                        "method" => "getCefrExamPrepLicenses",
                        "group" => "cefr",
                        "table" => "cd_trainer",
                        "type" => "text",
                        "multi_value" => true),
        "cefr_exam_license" => array(
                        "input" => "cefr_exam_licenses",
                        "method" => "getCefrExamLicenses",
                        "group" => "cefr",
                        "table" => "cd_trainer_el"),
        "other_competences" => array(
                        "input" => "textarea",
                        "required_fix_value" => 1,
                        "method" => "getOtherCompetences",
                        "group" => "cefr",
                        "table" => "cd_trainer",
                        "type" => "text"),
        "current_teaching_material" => array(
                        "input" => "textarea",
                        "required_fix_value" => 1,
                        "method" => "getCurrentTeachingMaterial",
                        "group" => "cefr",
                        "table" => "cd_trainer",
                        "type" => "clob"),
        "teaching_media" => array(
                        "input" => "teaching_media",
                        "method" => "getTeachingMedia",
                        "group" => "cefr",
                        "table" => "cd_trainer",
                        "type" => "text",
                        "multi_value" => true),
        "other_teaching_media" => array(
                        "input" => "textarea",
                        "required_fix_value" => 1,
                        "method" => "getOtherTeachingMedia",
                        "group" => "cefr",
                        "table" => "cd_trainer",
                        "type" => "text"),
        "teaching_methods" => array(
                        "input" => "textarea",
                        "required_fix_value" => 1,
                        "method" => "getTeachingMethods",
                        "group" => "cefr",
                        "table" => "cd_trainer",
                        "type" => "clob"),
        "trainer_role" => array(
                        "input" => "textarea",
                        "required_fix_value" => 1,
                        "method" => "getTrainerRole",
                        "group" => "cefr",
                        "table" => "cd_trainer",
                        "type" => "clob"),
        "my_potential" => array(
                        "input" => "textarea",
                        "required_fix_value" => 1,
                        "method" => "getMyPotential",
                        "group" => "cefr",
                        "table" => "cd_trainer",
                        "type" => "clob"),
        "scenario" => array(
                        "input" => "textarea",
                        "required_fix_value" => 1,
                        "method" => "getScenario",
                        "group" => "cefr",
                        "table" => "cd_trainer",
                        "type" => "clob"),
        "teaching_location" => array(
                        "input" => "textarea",
                        "required_fix_value" => 1,
                        "method" => "getTeachingLocation",
                        "group" => "cefr",
                        "table" => "cd_trainer",
                        "type" => "text"),
        "language" => array(
                        "input" => "language",
                        "method" => "getLanguage",
                        "required_hide" => true,
                        "visib_reg_hide" => true,
                        "course_export_hide" => true,
                        "group_export_hide" => true,
                        "group" => "settings")
        
        );
    // cdpatch end (only new fields)

    /**
     * Constructor
     */
    public function __construct($a_cd_plugin)
    {
        $this->skip_groups = array();
        $this->skip_fields = array();
        $this->cd_plugin = $a_cd_plugin;
        
        include_once("./Services/CD/classes/class.ilCDTechnicalLanguage.php");
        $this->tech_lang = ilCDTechnicalLanguage::getAll();
        
        include_once("./Services/CD/classes/class.ilCDTeachType.php");
        $this->teach_types = ilCDTeachType::getAll();
    }
    
    /**
    * Add standard fields to form
    */
    public function addStandardFieldsToForm($a_form, $a_user = null, array $custom_fields = null, $a_trainer = null)
    {
        $this->trainer = $a_trainer;
        if ($this->trainer) {
            $this->trainer_user = new ilObjUser($this->trainer->getId());
        }
        parent::addStandardFieldsToForm($a_form, $a_user, $custom_fields);
        
        $prefields = array("firstname", "lastname", "email", "center_id", "title", "gender");
        foreach ($prefields as $f) {
            if ($_GET[$f] != "") {
                $prefix = ($f != "center_id")
                    ? "usr_"
                    : "";
                $inp = $a_form->getItemByPostVar($prefix . $f);
                $inp->setValue($_GET[$f]);
            }
        }
        
        if ($this->trainer) {
            $this->setTrainerValuesIntoForm($a_form);
        }
    }

    
    public static function userSettingVisible($a_setting)
    {
        return true;
    }
    
    /**
     * Get standard user fields array
     */
    public function getStandardFields()
    {
        $fields = array();
        foreach (self::$user_field as $f => $p) {
            // skip hidden groups
            if (in_array($p["group"], $this->skip_groups) ||
                in_array($f, $this->skip_fields)) {
                continue;
            }
            $fields[$f] = $p;
        }

        include_once("./Services/CD/classes/class.cdUtil.php");
        if (cdUtil::isDAF()) {	// a17k540
            unset($fields["fax"]);
            unset($fields["residence_permit"]);
            unset($fields["work_permit"]);
            unset($fields["permit_license"]);
            unset($fields["driving_license"]);
            unset($fields["car_owner"]);
            unset($fields["instruction_language"]);
            unset($fields["lang_education"]);
            unset($fields["lang_education_other"]);
            unset($fields["cefr_knowledge"]);
            unset($fields["scenario"]);
        }
        //	var_dump($fields); exit;
        return $fields;
    }

    /**
     * Add form field
     *
     * @param
     * @return
     */
    public function addFormField($a_form, $f, $p, $a_user = false)
    {
        global $lng;
        
        $lv = (isset($p["lang_var"]) && $p["lang_var"] != "")
            ? $p["lang_var"]
            : $f;
            
        $m = $p["method"];

        switch ($p["input"]) {
            case "hidden":
                $hi = new ilHiddenInputGUI($f);
                //$hi->setValue();
                $a_form->addItem($hi);
                break;

            case "cd_sel_country":
                include_once("./Services/Form/classes/class.ilCountrySelectInputGUI.php");
                $ci = new ilCountrySelectInputGUI($lng->txt($lv), "usr_" . $f);
                if ($a_user) {
                    $ci->setValue($a_user->$m());
                }
                if ($f == "cd_sel_country") {
                    $ci->setRequired(true);
                }
                $a_form->addItem($ci);
                break;
                
            case "checkbox":
                include_once("./Services/Form/classes/class.ilCheckboxInputGUI.php");
                $ci = new ilCheckboxInputGUI($lng->txt($lv), "usr_" . $f);
                if ($a_user && $p["table"] == "") {
                    $ci->setValue($a_user->$m());
                }
                $a_form->addItem($ci);
                break;
                
            case "multi_language":
                $lng->loadLanguageModule("meta");
                include_once("./Services/MetaData/classes/class.ilMDLanguageItem.php");
                $tmp_options[""] = $lng->txt("please_select");
                foreach (ilMDLanguageItem::_getPossibleLanguageCodes() as $code) {
                    $tmp_options[$code] = $lng->txt('meta_l_' . $code);
                }
                asort($tmp_options, SORT_STRING);
                $si = new ilSelectInputGUI($lng->txt($lv), "usr_" . $f);
                $si->setOptions($tmp_options);
                $si->setMulti(true);
                $si->setRequired(true);
                $a_form->addItem($si);
                break;

            case "multi_language_skill":
                include_once("./Services/CD/classes/class.ilLangSkillInputGUI.php");
                $si = new ilLangSkillInputGUI($lng->txt($lv), "usr_" . $f);
                $si->setMulti(true);
                //$si->setRequired(true);
                $a_form->addItem($si);
                break;

            case "lesson_number":
                $options = array(
                    "" => $lng->txt("please_select"),
                    1 => "0-50",
                    2 => "50-100",
                    3 => "> 100"
                    );
                if (cdUtil::isDAF()) {	// a17k540
                    $options = array(
                        "" => $lng->txt("please_select"),
                        4 => "< 500",
                        5 => "500 - 1000",
                        6 => "> 1000"
                    );
                }
                $si = new ilSelectInputGUI($lng->txt($lv), "usr_" . $f);
                $si->setOptions($options);
                $si->setRequired(true);
                $a_form->addItem($si);
                break;
                
            case "further_education":
                //
                $ne = new ilNonEditableValueGUI("", "");
                $ne->setValue($lng->txt("further_education_desc"));
                $a_form->addItem($ne);

                $to = (int) date("Y");

                $start = 2006;

                for ($i = $start; $i <= $to; $i++) {
                    if (cdUtil::isDAF()) {	// a17k540
                        if ($i <= 2012 && $i > 2006) {
                            continue;
                        }
                    }

                    $txt = $i;
                    if ($i == $start) {
                        $txt = $lng->txt("cd_before");
                    }
                    
                    // checkbox
                    $cb = new ilCheckboxInputGUI($txt, "fe_cb_" . $i);
                    $a_form->addItem($cb);
                    
                    //
                    $ta = new ilTextAreaInputGUI("", "fe_ta_" . $i);
                    $ta->setCols(40);
                    $ta->setRows(5);
                    $cb->addSubItem($ta);
                }
                break;
                
            case "education":
                //
                $options = array(
                    "" => $lng->txt("please_select"),
                    "abitur" => $lng->txt("abitur_or_similar"),
                    "fh_bachelor" => $lng->txt("bachelor_or_similar"),
                    "uni_master" => $lng->txt("master_or_similar"),
                    "promotion_doktorat" => $lng->txt("promotion_or_similar"),
                    "ht_promotion" => $lng->txt("promotion_higher")
                    );
                if (cdUtil::isDAF()) {	// a17k540
                    $options = array(
                        "" => $lng->txt("please_select"),
                        "daf_certificate" => $lng->txt("daf_certificate"),
                        "abitur" => $lng->txt("abitur_or_similar"),
                        "fh_bachelor" => $lng->txt("bachelor_or_similar"),
                        "uni_master" => $lng->txt("master_or_similar"),
                        "promotion_doktorat" => $lng->txt("promotion_or_similar"),
                        "ht_promotion" => $lng->txt("promotion_higher")
                    );
                }
                $si = new ilSelectInputGUI($lng->txt("education"), "usr_general_education");
                $si->setOptions($options);
                $a_form->addItem($si);
                break;
                
            case "lang_education":
                //
                $options = array(
                    "" => $lng->txt("please_select"),
                    "telc" => "telc",
                    "tefl" => "TEFL",
                    "tesol" => "TESOL",
                    "celta" => "CELTA",
                    "delta" => "DELTA",
                    "delf" => "DELF",
                    );
                $si = new ilSelectInputGUI($lng->txt("language_education"), "usr_lang_education");
                $si->setOptions($options);
                $a_form->addItem($si);
                break;
                
            case "tech_lang_exp":
                
                // technical languages experience
                $ne = new ilNonEditableValueGUI($lng->txt("teach_exp_tech_lang"), "");
                $ne->setValue("");
                $a_form->addItem($ne);
                

                //
                $options = array(
                    "" => $lng->txt("please_select"),
                    1 => $lng->txt("none"),
                    2 => $lng->txt("little"),
                    3 => $lng->txt("much")
                    );
                if (cdUtil::isDAF()) {	// a17k540
                    $options = array(
                        "" => $lng->txt("please_select"),
                        3 => $lng->txt("yes"),
                        1 => $lng->txt("no"),
                    );
                }
                foreach ($this->tech_lang as $k => $tl) {
                    $si = new ilSelectInputGUI($this->cd_plugin->txt($tl), "teach_exp_tech_lang_" . $k);
                    $si->setOptions($options);
                    $si->setRequired(true);
                    $ne->addSubItem($si);
                }
                
                break;
                
            case "teach_type_exp":

                // technical languages experience
                $ne = new ilNonEditableValueGUI($lng->txt("teach_type_exp"), "");
                $ne->setValue("");
                $a_form->addItem($ne);
                
                // teach types

                $options = array(
                    "" => $lng->txt("please_select"),
                    1 => $lng->txt("none"),
                    2 => $lng->txt("little"),
                    3 => $lng->txt("much")
                    );
                
                foreach ($this->teach_types as $k => $tt) {
                    $si = new ilSelectInputGUI($lng->txt($tt), "teach_type_" . $k);
                    $si->setOptions($options);
                    $si->setRequired(true);
                    $ne->addSubItem($si);
                }

                break;

            case "cefr_knowledge":

                //
                $options = array(
                    "" => $lng->txt("please_select"),
                    1 => $lng->txt("not"),
                    2 => $lng->txt("little"),
                    3 => $lng->txt("good")
                    );
                
                $si = new ilSelectInputGUI($lng->txt("cefr_knowledge"), "usr_cefr_knowledge");
                $si->setOptions($options);
                $si->setRequired(true);
                $a_form->addItem($si);

                break;
            
            case "cefr_levels":

                $options = array(
                    "A1" => "A1",
                    "A2" => "A2",
                    "B1" => "B1",
                    "B2" => "B2",
                    "C1" => "C1",
                    "C2" => "C2"
                    );
                
                include_once("./Services/Form/classes/class.ilMultiSelectInputGUI.php");
                $si = new ilMultiSelectInputGUI($lng->txt("favorite_cefr_levels"), "favorite_cefr_levels");
                $si->setOptions($options);
                $a_form->addItem($si);
                break;

            case "cefr_prep_licenses":
                include_once("./Services/CD/classes/class.ilCDLangExamLicense.php");
                $options = ilCDLangExamLicense::getLicenses();
                include_once("./Services/Form/classes/class.ilMultiSelectInputGUI.php");
                $si = new ilMultiSelectInputGUI($lng->txt("cefr_exam_prep_license"), "cefr_exam_prep_license");
                $si->setOptions($options);
                $a_form->addItem($si);
                break;
                
            case "cefr_exam_licenses":
                include_once("./Services/CD/classes/class.ilLangLicenseInputGUI.php");
                $si = new ilLangLicenseInputGUI($lng->txt($lv), $f);
                $si->setMulti(true);
                $a_form->addItem($si);
                break;
                
            case "teaching_media":
                $options = array(
                    "print" => $lng->txt("tm_print"),
                    "cd" => $lng->txt("tm_cd"),
                    "dvd" => $lng->txt("tm_dvd"),
                    "internet" => $lng->txt("tm_internet"),
                    "mp3" => $lng->txt("tm_mp3"),
                    "skype" => $lng->txt("tm_skype"),
                    "podcast" => $lng->txt("tm_podcasts")
                    );
                if (cdUtil::isDAF()) {	// a17k540
                    $options["apps"] = $lng->txt("tm_apps");
                }
                include_once("./Services/Form/classes/class.ilMultiSelectInputGUI.php");
                $si = new ilMultiSelectInputGUI($lng->txt("teaching_media"), "teaching_media");
                $si->setOptions($options);
                $a_form->addItem($si);
                break;
        }
    }

    
    /**
     * Create trainer user from form
     *
     * @param
     * @return
     */
    public function createUserFromForm($a_form, $a_user)
    {
        global $ilSetting;
        
        $this->userObj = $a_user;
                
        // cd trainer specific values
        $this->setUserValuesFromForm($this->userObj, $a_form);
        
        // standard behaviour follows...
        
        $this->userObj->setFullName();

        $this->userObj->setTitle($this->userObj->getFullname());
        $this->userObj->setDescription($this->userObj->getEmail());

        $password = $a_form->getInput("usr_password");
        $this->userObj->setPasswd($password);

        $this->userObj->setTimeLimitOwner(7);
        $this->userObj->setTimeLimitUnlimited(1);
        $this->userObj->setTimeLimitUntil(time());
        $this->userObj->setTimeLimitFrom(time());
        $this->userObj->create();
        
        $this->userObj->setActive(0);
        $this->userObj->updateOwner();
        $this->userObj->setLastPasswordChangeTS(time());
        $this->userObj->saveAsNew();
        $this->userObj->writeAccepted();

        $this->userObj->setLanguage($a_form->getInput('usr_language'));
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
        
        
        // cd trainer values
        include_once("./Services/CD/classes/class.ilCDTrainer.php");
        $trainer = new ilCDTrainer();
        $trainer->setId($this->userObj->getId());
        $this->setTrainerValuesFromForm($trainer, $a_form);
        $trainer->create();
        
        // role stuff
        include_once("./Services/CD/classes/class.ilCDPermWrapper.php");
        ilCDPermWrapper::initTrainerUserRoles($this->userObj->getId(), $trainer->cd_trainer["center_id"]);

        return $password;
    }
    
    /**
     * Update user from form
     *
     * @param
     * @return
     */
    public function updateUserFromForm($a_form, $a_user)
    {
        global $ilSetting;
        
        $this->userObj = $a_user;
                
        // cd trainer specific values
        $this->setUserValuesFromForm($this->userObj, $a_form);
        
        // standard behaviour follows...
        $this->userObj->setFullName();
        $this->userObj->setTitle($this->userObj->getFullname());
        $this->userObj->setDescription($this->userObj->getEmail());
        $this->userObj->setLanguage($a_form->getInput('usr_language'));
        $this->userObj->update();
                
        // cd trainer values
        include_once("./Services/CD/classes/class.ilCDTrainer.php");
        $trainer = new ilCDTrainer($this->userObj->getId());
        $this->setTrainerValuesFromForm($trainer, $a_form);
        $trainer->update();
    }
    
    /**
     * Set user values from form
     *
     * @param
     * @return
     */
    public function setUserValuesFromForm($a_user, $a_form)
    {
        $a_user->setLogin($_POST["username"]);
        $a_user->setGender($_POST["usr_gender"]);
        $a_user->setUTitle($_POST["usr_title"]);
        $a_user->setFirstname($_POST["usr_firstname"]);
        $a_user->setLastname($_POST["usr_lastname"]);
        $a_user->setSelectedCountry($_POST["usr_cd_sel_country"]);
        $a_user->setPhoneOffice($_POST["usr_phone_office"]);
        $a_user->setPhoneMobile($_POST["usr_phone_mobile"]);
        $a_user->setFax($_POST["usr_fax"]);
        $a_user->setEmail($_POST["usr_email"]);
        $a_user->setZipCode($_POST["usr_zipcode"]);
        $a_user->setCountry($_POST["usr_country"]);
        $a_user->setCity($_POST["usr_city"]);
        $a_user->setStreet($_POST["usr_street"]);
        
        $birthday_obj = $a_form->getItemByPostVar("usr_birthday");
        if ($birthday_obj) {
            $birthday = $a_form->getInput("usr_birthday");
            //$birthday = $birthday["date"];

            // when birthday was not set, array will not be substituted with string by ilBirthdayInputGui
            //if(!is_array($birthday))
            //{
            $a_user->setBirthday($birthday);
            //}
        }

        // todo: password
    }

    /**
     * Set trainer values from form
     *
     * @param
     * @return
     */
    public function setTrainerValuesFromForm($a_trainer, $a_form)
    {
        $fields = $this->getStandardFields();
        $trainer = $a_trainer->cd_trainer;
        foreach ($fields as $f => $def) {
            if ($def["table"] == "cd_trainer" && $f != "center_id") {
                $trainer[$f] = $_POST["usr_" . $f];
            }
        }
        if ($_POST["center_id"] > 0) {
            $trainer["center_id"] = $_POST["center_id"];
        }
        $a_trainer->cd_trainer = $trainer;

        // mother tongue
        $a_trainer->cd_trainer_mtl = array();
        if (is_array($_POST["usr_mother_tongue"])) {
            foreach ($_POST["usr_mother_tongue"] as $l) {
                if ($l != "") {
                    $a_trainer->cd_trainer_mtl[$l] = $l;
                }
            }
        }
        
        // instructional languages
        $a_trainer->cd_trainer_il = array();
        if (is_array($_POST["usr_instruction_language"])) {
            foreach ($_POST["usr_instruction_language"] as $l) {
                if ($l != "") {
                    $a_trainer->cd_trainer_il[$l] = $l;
                }
            }
        }

        // language skills
        $a_trainer->cd_trainer_ls = array();
        if (is_array($_POST["usr_language_skills"])) {
            foreach ($_POST["usr_language_skills"] as $k => $l) {
                if ($_POST["usr_language_skills_l"][$k] != "") {
                    if ($l != "" && $_POST["usr_language_skills_l"][$k] != "") {
                        $a_trainer->cd_trainer_ls[$l] = array("lang" => $l,
                            "slevel" => $_POST["usr_language_skills_l"][$k]);
                    }
                }
            }
        }
        
        // further education
        $a_trainer->cd_trainer_fe = array();
        foreach ($_POST as $k => $v) {
            if (substr($k, 0, 6) == "fe_ta_") {
                $year = substr($k, 6);
                if ($_POST["fe_ta_" . $year] != "" && $_POST["fe_cb_" . $year]) {
                    $a_trainer->cd_trainer_fe[$year] = array("year" => $year,
                        "description" => $_POST["fe_ta_" . $year]);
                }
            }
        }
        
        // teach experience technical languages
        $a_trainer->cd_trainer_tl = array();
        foreach ($this->tech_lang as $k => $txt) {
            $a_trainer->cd_trainer_tl[$k] = array("disc" => $k,
                "val" => $_POST["teach_exp_tech_lang_" . $k]);
        }
        
        // teaching types experience
        $a_trainer->cd_trainer_tt = array();
        foreach ($this->teach_types as $k => $txt) {
            $a_trainer->cd_trainer_tt[$k] = array("type" => $k,
                "val" => $_POST["teach_type_" . $k]);
        }

        // favourite levels
        $a_trainer->cd_trainer_mv["favorite_cefr_levels"] = array();
        if (is_array($_POST["favorite_cefr_levels"])) {
            foreach ($_POST["favorite_cefr_levels"] as $level) {
                $a_trainer->cd_trainer_mv["favorite_cefr_levels"][] = $level;
            }
        }

        // preparation licenses
        $a_trainer->cd_trainer_mv["cefr_exam_prep_license"] = array();
        if (is_array($_POST["cefr_exam_prep_license"])) {
            foreach ($_POST["cefr_exam_prep_license"] as $lic) {
                $a_trainer->cd_trainer_mv["cefr_exam_prep_license"][] = $lic;
            }
        }

        // exam licenses
        $a_trainer->cd_trainer_el = array();
        if (is_array($_POST["cefr_exam_license"])) {
            foreach ($_POST["cefr_exam_license"] as $k => $lic) {
                if ($_POST["cefr_exam_license_d"][$k] != "" && $lic != "") {
                    $date = explode(".", $_POST["cefr_exam_license_d"][$k]);
                    
                    $a_trainer->cd_trainer_el[$lic] = array("license" => $lic,
                        "end_date" => $date[2] . "-" . $date[1] . "-" . $date[0]);
                }
            }
        }
        
        // teaching media
        $a_trainer->cd_trainer_mv["teaching_media"] = array();
        if (is_array($_POST["teaching_media"])) {
            foreach ($_POST["teaching_media"] as $tm) {
                $a_trainer->cd_trainer_mv["teaching_media"][] = $tm;
            }
        }
    }

    /**
     * Get values from trainer
     *
     * @param
     * @return
     */
    public function setTrainerValuesIntoForm($a_form)
    {
        $this->skipField("password");
        $this->skipField("center_id");
        foreach ($this->getStandardFields() as $f => $def) {
            $item = null;
            if ($def["table"] == "cd_trainer") {
                $item = $a_form->getItemByPostVar("usr_" . $f);
                $val = $this->trainer->cd_trainer[$f];
            } elseif ($def["table"] == "usr_data" || $f == "language") {
                $m = $def["method"];

                if ($f == "username") {
                    $item = $a_form->getItemByPostVar("ne_un");
                } else {
                    $item = $a_form->getItemByPostVar("usr_" . $f);
                }

                $val = $this->trainer_user->$m();
            }
            
            //		echo "-$f-";
            if (is_object($item)) {
                //		echo "+".$this->trainer->cd_trainer[$f]."+";
                switch ($def["input"]) {
                    case "checkbox":
                        $item->setChecked($val);
                        break;
                        
                    case "birthday":
                        //$item->setValueByArray(array("usr_birthday" => array("date" => $val)));
                        $item->setDate(new ilDate($val, IL_CAL_DATE));
                        break;
                        
                    default:
                        $item->setValue($val);
                        break;
                }
            } else {
                switch ($f) {
                    case "mother_tongue":
                        $item = $a_form->getItemByPostVar("usr_" . $f);
                        $item->setValue($this->trainer->cd_trainer_mtl);
                        break;
                        
                    case "instruction_language":
                        $item = $a_form->getItemByPostVar("usr_" . $f);
                        $item->setValue($this->trainer->cd_trainer_il);
                        break;
                        
                    case "language_skills":
                        $langs = array();
                        $levels = array();
                        foreach ($this->trainer->cd_trainer_ls as $ls) {
                            $langs[] = $ls["lang"];
                            $levels[] = $ls["slevel"];
                        }
                        $item = $a_form->getItemByPostVar("usr_" . $f);
                        $item->setValue($langs);
                        $item->setLevelValues($levels);
                        break;
                        
                    case "further_education":
                        foreach ($this->trainer->cd_trainer_fe as $fe) {
                            if (trim($fe["description"]) != "") {
                                $y = $fe["year"];
                                $cb = $a_form->getItemByPostVar("fe_cb_" . $y);
                                $cb->setChecked(true);
                                $t = $a_form->getItemByPostVar("fe_ta_" . $y);
                                $t->setValue($fe["description"]);
                            }
                        }
                        break;
                        
                    case "teach_exp_tech_lang":
                        foreach ($this->trainer->cd_trainer_tl as $k => $v) {
                            $s = $a_form->getItemByPostVar("teach_exp_tech_lang_" . $k);
                            $s->setValue($v["val"]);
                        }
                        break;
                        
                    case "teach_type_exp":
                        foreach ($this->trainer->cd_trainer_tt as $k => $v) {
                            $s = $a_form->getItemByPostVar("teach_type_" . $k);
                            $s->setValue($v["val"]);
                        }
                        break;
                        
                    case "favorite_cefr_levels":
                    case "cefr_exam_prep_license":
                    case "teaching_media":
                        $i = $a_form->getItemByPostVar($f);
                        $i->setValue($this->trainer->cd_trainer_mv[$f]);
                        break;
                        
                    case "cefr_exam_license":
                        $lics = array();
                        $dates = array();
                        foreach ($this->trainer->cd_trainer_el as $el) {
                            $lics[] = $el["license"];
                            $dates[] = substr($el["end_date"], 8, 2) . "." . substr($el["end_date"], 5, 2) .
                                "." . substr($el["end_date"], 0, 4);
                        }
                        $item = $a_form->getItemByPostVar($f);
                        $item->setValue($lics);
                        $item->setDateValues($dates);

                        break;
                        
                    default:
                        echo "-$f-";
                }
            }
        }
    }

    /**
     * Log editing
     *
     * @param
     * @return
     */
    public static function logEditing($a_trainer_id)
    {
        global $ilDB, $ilUser;

        $ilDB->manipulate("INSERT INTO cd_trainer_log  " .
            "(user_id, ts, changed_by) VALUES (" .
            $ilDB->quote($a_trainer_id, "integer") . "," .
            $ilDB->quote(ilUtil::now(), "timestamp") . "," .
            $ilDB->quote($ilUser->getId(), "integer") .
            ")");
    }
    
    /**
     * Get change log
     *
     * @param
     * @return
     */
    public static function getChangeLog($a_trainer_id)
    {
        global $ilDB;
        
        $set = $ilDB->query(
            "SELECT * FROM cd_trainer_log " .
            " WHERE user_id = " . $ilDB->quote($a_trainer_id, "integer") .
            " ORDER BY ts DESC"
        );
        $changes = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $changes[] = $rec;
        }
        return $changes;
    }
}
