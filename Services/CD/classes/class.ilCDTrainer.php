<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * CD trainer application class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup
 */
class ilCDTrainer
{
    // id
    protected $id;
    public $cd_trainer;
    public $cd_trainer_mtl = array();
    public $cd_trainer_il = array();
    public $cd_trainer_ls = array();
    public $cd_trainer_fe = array();
    public $cd_trainer_tl = array();
    public $cd_trainer_tt = array();
    public $cd_trainer_mv = array();
    public $cd_trainer_el = array();
    

    /**
     * Constructor
     *
     * @param	integer	$a_id	CD trainer id
     */
    public function __construct($a_id = 0)
    {
        // get cd plugin
        global $ilPluginAdmin;

        $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
        $plugin_html = false;
        foreach ($pl_names as $pl) {
            if ($pl == "CD") {
                $this->cd_plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
            }
        }

        if ($a_id > 0) {
            $this->setId($a_id);
            $this->read();
        }
    }

    /**
     * Set id
     *
     * @param	integer	$a_val	id
     */
    public function setId($a_val)
    {
        $this->id = $a_val;
    }

    /**
     * Get id
     *
     * @return	integer	id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Read instance data from database
     */
    protected function read()
    {
        global $ilDB;

        // cd_trainer data
        $set = $ilDB->query(
            "SELECT * FROM cd_trainer WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        $this->cd_trainer = $rec;
        
        $fee = @unserialize($rec["fee"]);
        if (!is_array($fee)) {
            $fee = array();
        }
        $this->cd_trainer["fee"] = $fee;
        
        // cd_trainer_mtl data (mother tongue languages)
        $set = $ilDB->query(
            "SELECT * FROM cd_trainer_mtl WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->cd_trainer_mtl[$rec["lang"]] = $rec["lang"];
        }

        // cd_trainer_il data (instruction language)
        $set = $ilDB->query(
            "SELECT * FROM cd_trainer_il WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->cd_trainer_il[$rec["lang"]] = $rec["lang"];
        }

        // cd_trainer_ls data (language skills)
        $set = $ilDB->query(
            "SELECT * FROM cd_trainer_ls WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->cd_trainer_ls[$rec["lang"]] = $rec;
        }

        // cd_trainer_fe data (further education)
        $set = $ilDB->query(
            "SELECT * FROM cd_trainer_fe WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->cd_trainer_fe[$rec["year"]] = $rec;
        }

        // cd_trainer_tl data (teaching experience in technical languages)
        $set = $ilDB->query(
            "SELECT * FROM cd_trainer_tl WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->cd_trainer_tl[$rec["disc"]] = array("val" => $rec["val"], "disc" => $rec["disc"]);
        }

        // cd_trainer_tt data (teaching type experience)
        $set = $ilDB->query(
            "SELECT * FROM cd_trainer_tt WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->cd_trainer_tt[$rec["type"]] = array("val" => $rec["val"], "type" => $rec["type"]);
        }

        // favourite levels
        $this->cd_trainer_mv["favorite_cefr_levels"] = explode(";", $this->cd_trainer["favorite_cefr_levels"]);

        // preparation licenses
        $this->cd_trainer_mv["cefr_exam_prep_license"] = explode(";", $this->cd_trainer["cefr_exam_prep_license"]);

        // exam licenses
        $set = $ilDB->query(
            "SELECT * FROM cd_trainer_el WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->cd_trainer_el[$rec["license"]] = $rec;
        }

        // teaching media
        $this->cd_trainer_mv["teaching_media"] = explode(";", $this->cd_trainer["teaching_media"]);
    }

    /**
     * Create CD trainer
     */
    public function create()
    {
        global $ilDB;

        $id = $this->getId();

        // cd_trainer table
        $fields = $this->getMainDBFields();

        $fields["id"] = array("integer", $id);

        $ilDB->insert("cd_trainer", $fields);
        
        $this->writeOtherTables();
    }

    /**
     * Get main db fields
     *
     * @param
     * @return
     */
    public function getMainDbFields()
    {
        include_once("./Services/CD/classes/class.ilCDTrainerProfile.php");
        $pro = new ilCDTrainerProfile($this->cd_plugin);
        $sfields = $pro->getStandardFields();
        //		var_dump($sfields); exit;
        foreach ($sfields as $f => $def) {
            if ($def["table"] == "cd_trainer") {
                if (!$def["multi_value"]) {
                    //echo "-".$this->cd_trainer[$f];
                    $val = $this->cd_trainer[$f];
                } else {
                    if (is_array($this->cd_trainer_mv[$f])) {
                        $val = implode(";", $this->cd_trainer_mv[$f]);
                    } else {
                        $val = "";
                    }
                }
                if ($def["type"] == "integer") {
                    $fields[$f] = array("integer", (int) $val);
                } else {
                    $fields[$f] = array($def["type"], $val);
                }
            }
        }
        
        $fields["notes"] = array("clob", $this->cd_trainer["notes"]);
        $fields["qm"] = array("clob", $this->cd_trainer["qm"]);
        $fields["entry_date"] = array("date", $this->cd_trainer["entry_date"]);
        
        $fields["interview"] = array("text", $this->cd_trainer["interview"]);
        $fields["competence_eval"] = array("integer", (int) $this->cd_trainer["competence_eval"]);
        $fields["appearance"] = array("integer", (int) $this->cd_trainer["appearance"]);
        $fields["overall_impression"] = array("clob", $this->cd_trainer["overall_impression"]);
        $fields["supplier_eval"] = array("text", $this->cd_trainer["supplier_eval"]);
        $fields["add_arrangement"] = array("text", $this->cd_trainer["add_arrangement"]);
        $fields["gen_agreement_out"] = array("text", $this->cd_trainer["gen_agreement_out"]);
        $fields["gen_agreement_in"] = array("text", $this->cd_trainer["gen_agreement_in"]);
        $fields["train_guide_handout"] = array("text", $this->cd_trainer["train_guide_handout"]);
        
        $fee = (is_array($this->cd_trainer["fee"]))
            ? $this->cd_trainer["fee"]
            : array();
        $fee = ilUtil::sortArray($fee, "date", "asc");
        $fields["fee"] = array("clob", serialize($fee));

        $fields["former"] = array("integer", (int) $this->cd_trainer["former"]);
        
        return $fields;
    }
    
    
    /**
     * Write other tables
     *
     * @param
     * @return
     */
    public function writeOtherTables()
    {
        global $ilDB;
        
        // mother tongue
        $ilDB->manipulate("DELETE FROM cd_trainer_mtl WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer"));

        foreach ($this->cd_trainer_mtl as $l) {
            $ilDB->manipulate("INSERT INTO cd_trainer_mtl " .
                "(id, lang) VALUES (" .
                $ilDB->quote($this->getId(), "integer") . "," .
                $ilDB->quote($l, "text") .
                ")");
        }
        
        // instruction language
        $ilDB->manipulate("DELETE FROM cd_trainer_il WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer"));
        foreach ($this->cd_trainer_il as $l) {
            $ilDB->manipulate("INSERT INTO cd_trainer_il " .
                "(id, lang) VALUES (" .
                $ilDB->quote($this->getId(), "integer") . "," .
                $ilDB->quote($l, "text") .
                ")");
        }
        
        // language skills
        $ilDB->manipulate("DELETE FROM cd_trainer_ls WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer"));
        foreach ($this->cd_trainer_ls as $l) {
            $ilDB->manipulate("INSERT INTO cd_trainer_ls " .
                "(id, lang, slevel) VALUES (" .
                $ilDB->quote($this->getId(), "integer") . "," .
                $ilDB->quote($l["lang"], "text") . "," .
                $ilDB->quote($l["slevel"], "text") .
                ")");
        }
        
        // further education
        $ilDB->manipulate("DELETE FROM cd_trainer_fe WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer"));
        foreach ($this->cd_trainer_fe as $l) {
            $ilDB->manipulate("INSERT INTO cd_trainer_fe " .
                "(id, year, description) VALUES (" .
                $ilDB->quote($this->getId(), "integer") . "," .
                $ilDB->quote($l["year"], "integer") . "," .
                $ilDB->quote($l["description"], "text") .
                ")");
        }
        
        // teach experience technical languages
        $ilDB->manipulate("DELETE FROM cd_trainer_tl WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer"));
        foreach ($this->cd_trainer_tl as $l) {
            $ilDB->manipulate("INSERT INTO cd_trainer_tl " .
                "(id, disc, val) VALUES (" .
                $ilDB->quote($this->getId(), "integer") . "," .
                $ilDB->quote($l["disc"], "integer") . "," .
                $ilDB->quote($l["val"], "integer") .
                ")");
        }
        
        // teach type experience
        $ilDB->manipulate("DELETE FROM cd_trainer_tt WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer"));
        foreach ($this->cd_trainer_tt as $l) {
            $ilDB->manipulate("INSERT INTO cd_trainer_tt " .
                "(id, type, val) VALUES (" .
                $ilDB->quote($this->getId(), "integer") . "," .
                $ilDB->quote($l["type"], "integer") . "," .
                $ilDB->quote($l["val"], "integer") .
                ")");
        }
        
        // examination licenses
        $ilDB->manipulate("DELETE FROM cd_trainer_el WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer"));
        foreach ($this->cd_trainer_el as $l) {
            $ilDB->manipulate("INSERT INTO cd_trainer_el " .
                "(id, license, end_date) VALUES (" .
                $ilDB->quote($this->getId(), "integer") . "," .
                $ilDB->quote($l["license"], "text") . "," .
                $ilDB->quote($l["end_date"], "date") .
                ")");
        }
    }
    
    
    /**
     * Update CD trainer
     */
    public function update()
    {
        global $ilDB;

        $id = $this->getId();
        if (!$id) {
            return false;
        }

        $fields = $this->getMainDBFields();

        $ilDB->update(
            "cd_trainer",
            $fields,
            array("id" => array("integer", $id))
        );
        
        $this->writeOtherTables();

        return true;
    }

    /**
     * Delete CD trainer
     */
    public function delete()
    {
        global $ilDB;

        $tables = array("cd_trainer", "cd_trainer_mtl",
            "cd_trainer_il", "cd_trainer_ls", "cd_trainer_fe",
            "cd_trainer_tl", "cd_trainer_tt", "cd_trainer_el");
        
        foreach ($tables as $t) {
            $ilDB->manipulate(
                "DELETE FROM " . $t . " WHERE "
                . " id = " . $ilDB->quote($this->getId(), "integer")
            );
        }
    }

    /**
     * Get all CD trainers
     *
     * @return	array	CD trainer data
     */
    public static function getAll($a_filter)
    {
        global $ilDB, $lng;

        $lng->loadLanguageModule("meta");

        $q = "SELECT u.login, u.create_date, u.title, u.gender, u.birthday, u.sel_country, u.country, u.phone_office, u.phone_mobile, u.fax, u.email, u.usr_id, u.firstname, u.lastname, u.zipcode, u.city, u.street, u.active, " .
            "u.sel_country, t.* FROM cd_trainer t JOIN usr_data u ON (t.id = u.usr_id)";

        $where = " WHERE ";
        //var_dump($a_filter);
        if (trim($a_filter["name"]) != "") {
            $q .= $where . "(" . $ilDB->like("u.firstname", "text", "%" . trim($a_filter["name"]) . "%") .
                " OR " . $ilDB->like("u.lastname", "text", "%" . trim($a_filter["name"]) . "%") . ")";
            $where = " AND ";
        }

        if (trim($a_filter["zipcode"]) != "") {
            $q .= $where . $ilDB->like("u.zipcode", "text", trim($a_filter["zipcode"]) . "%");
            $where = " AND ";
        }

        if ($a_filter["create_date_start"] != "" && $a_filter["create_date_end"] &&
            (
                ($a_filter["create_date_start"] != $a_filter["create_date_end"]) ||
                $a_filter["create_date_start"] != date("Y-m-d", time())
            )
        ) {
            $q .= $where . "u.create_date >= " . $ilDB->quote($a_filter["create_date_start"] . " 00:00:00", "timestamp") .
                " AND u.create_date <= " . $ilDB->quote($a_filter["create_date_end"] . " 23:59:59", "timestamp");
            $where = " AND ";
        }

        if (trim($a_filter["city"]) != "") {
            $q .= $where . $ilDB->like("u.city", "text", "%" . trim($a_filter["city"]) . "%");
            $where = " AND ";
        }

        if ($a_filter["center_id"] > 0) {
            $q .= $where . " t.center_id = " . $ilDB->quote($a_filter["center_id"], "integer");
            $where = " AND ";
        }

        // empty or 0 brings current trainers, "" brings both, 1 brings former trainers
        $former = 0;
        if (!isset($a_filter["former"]) || $a_filter["former"] !== "") {
            if ($a_filter["former"] == 1) {
                $former = 1;
            }
            $q .= $where . " t.former = " . $ilDB->quote($former, "integer");
            $where = " AND ";
        }
        
        //echo "-$q-";
        $set = $ilDB->query($q);
        

        $items = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            // cd_trainer_mtl data (mother tongue languages)
            $set2 = $ilDB->query(
                "SELECT * FROM cd_trainer_mtl WHERE " .
                " id = " . $ilDB->quote($rec["usr_id"], "integer")
            );
            $mtl = array();
            $mtl_txt = array();
            while ($rec2 = $ilDB->fetchAssoc($set2)) {
                $mtl[] = $rec2["lang"];
                $mtl_txt[] = $lng->txt("meta_l_" . $rec2["lang"]);
            }
            $rec["mother_tongues"] = implode(", ", $mtl);
            $rec["mother_tongues_txt"] = implode(", ", $mtl_txt);
            
            // cd_trainer_il data (instruction languages)
            $set2 = $ilDB->query(
                "SELECT * FROM cd_trainer_il WHERE " .
                " id = " . $ilDB->quote($rec["usr_id"], "integer")
            );
            $il = array();
            $il_txt = array();
            $to_del = false;
            if ($a_filter["instr_lang"] != "") {
                $to_del = true;
            }
            while ($rec2 = $ilDB->fetchAssoc($set2)) {
                $il[] = $rec2["lang"];
                $il_txt[] = $lng->txt("meta_l_" . $rec2["lang"]);
                if ($a_filter["instr_lang"] != "" && $rec2["lang"] == $a_filter["instr_lang"]) {
                    $to_del = false;
                }
            }
            $rec["instruction_languages"] = implode(", ", $il);
            $rec["instruction_languages_txt"] = implode($il_txt);

            // cd_trainer_tl technical languages
            $set2 = $ilDB->query("SELECT * FROM cd_trainer_tl WHERE " .
                " id = " . $ilDB->quote($rec["usr_id"], "integer"));
            $tl = array();
            $tl_keep = false;
            //			echo "<br>check ".$rec["usr_id"];
            while ($rec2 = $ilDB->fetchAssoc($set2)) {
                $tl[] = $rec2;
                //				echo "<br>- ".$rec["tech_lang"]."-".$rec2["disc"]."-".$rec2["val"];
                if ($a_filter["tech_lang"] > 0 && $rec2["disc"] == $a_filter["tech_lang"]
                    && $rec2["val"] > 1) {
                    $tl_keep = true;
                }
            }
            $rec["technical_languages_arr"] = $tl;

            if ($a_filter["tech_lang"] > 0 && !$tl_keep) {
                $to_del = true;
            }

            
            $rec["name"] = $rec["lastname"] . ", " . $rec["firstname"];
            if (!$to_del) {
                $items[] = $rec;
            }
        }
        return $items;
    }

    /**
     * Lookup property
     *
     * @param	integer	$a_id	CD trainer id
     * @param	string	$a_prop	property
     *
     * @return	mixed	property value
     */
    protected static function lookupProperty($a_id, $a_prop)
    {
        global $ilDB;

        $set = $ilDB->query(
            "SELECT $a_prop FROM cd_trainer WHERE " .
            " id = " . $ilDB->quote($a_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return $rec[$a_prop];
    }

    /**
     * Lookup center id
     *
     * @param	integer	CD trainer id
     * @return	integer center id
     */
    public static function lookupCenterId($a_id)
    {
        return ilCDTrainer::lookupProperty($a_id, "center_id");
    }
    
    /**
     * Excel export
     *
     * @param
     * @return
     */
    public static function excelExport($a_pl, $a_offset = 0, $a_limit = 0)
    {
        global $lng, $log;
        
        $lng->loadLanguageModule("cd");
        $lng->loadLanguageModule("meta");
        
        $trainers = self::getAll(array());

        $limit = (int) $a_limit;
        $offset = (int) $a_offset;
        $filter = array();
        foreach (explode(":", $_GET["tfilter"]) as $f) {
            $filter[] = (int) $f;
        }
        
        $a_pl->includeClass("class.cdCenter.php");
        
        include_once("./Services/User/classes/class.ilUserQuery.php");
        
        include_once "./Services/Excel/classes/class.ilExcel.php";
        $excel = new ilExcel();
        $excel->addSheet("Trainer");

        //$workbook->setVersion(8); // Use Excel97/2000 Format
        //include_once ("./Services/Excel/classes/class.ilExcelUtils.php");
        
        //$ws = $workbook->addWorksheet();
        
        include_once("./Services/CD/classes/class.ilCDTrainerProfile.php");
        $tr_prof = new ilCDTrainerProfile($a_pl);
        $tr_prof->skipField("password");
        $tr_prof->skipField("language");
        $fields = $tr_prof->getStandardFields();
        
        $fields["notes"] = array("table" => "cd_trainer", "type" => "clob");
        $fields["qm"] = array("table" => "cd_trainer", "type" => "clob");
        $fields["entry_date"] = array("table" => "cd_trainer", "type" => "date");
        $fields["fee"] = array("table" => "cd_trainer", "type" => "clob");
        
        $fields["interview"] = array("table" => "cd_trainer", "type" => "text");
        $fields["competence_eval"] = array("table" => "cd_trainer", "type" => "integer");
        $fields["appearance"] = array("table" => "cd_trainer", "type" => "integer");
        $fields["overall_impression"] = array("table" => "cd_trainer", "type" => "clob");
        $fields["supplier_eval"] = array("table" => "cd_trainer", "type" => "text");
        $fields["add_arrangement"] = array("table" => "cd_trainer", "type" => "text");
        $fields["gen_agreement_out"] = array("table" => "cd_trainer", "type" => "text");
        $fields["gen_agreement_in"] = array("table" => "cd_trainer", "type" => "text");
        $fields["train_guide_handout"] = array("table" => "cd_trainer", "type" => "text");
        
        /* Mail, 2016-02-05
        - Berufserfahrung als Sprachtrainer/in
        - Berufserfahrung in anderen Berufen
        - Unterrichtserfahrung
        - Sonstige Fachsprachen
        - Weitere Kompetenzen, Kenntnisse, Erfahrungen
        - Ich arbeite in meinen Unterrichten mit folgenden aktuellen Lehrwerken
        - Sonstige Medien *
        - Ich verwende folgende Arbeitsformen und -methoden *
        - Meine Rolle als Trainer/in im Fremdsprachenunterricht sehe ich so *
        - Mein Entwicklungspotential *
        - Szenario: "Ich unterrichte für die Carl-Duisberg Centren einen Firmenkunden. Der Auftraggeber dieses Trainings und/oder ein pädagogischer Mitarbeiter der Carl Duisberg Centren möchte meinen Unterricht hospitieren." *
        - Einsatzregion/en, Orte*
         */
        $del = array(
            "work_exp_lang_train",
            "work_experience_other",
            "teaching_experience",
            "other_tech_lang",
            "other_competences",
            "current_teaching_material",
            "other_teaching_media",
            "teaching_methods",
            "trainer_role",
            "my_potential",
            "scenario",
            "teaching_location",

            "further_education",
            "general_education_other",
            "lang_education_other"
        );
        foreach ($del as $del_id) {
            //			unset($fields[$del_id]);
        }
        //var_dump($fields);
        // header row
        $col = 0;
        
        foreach ($fields as $f => $def) {
            $txt = "";
            switch ($f) {
                case "center_id":
                    $txt = $a_pl->txt("center");
                    break;
                    
                case "lang_education":
                    $txt = $a_pl->txt("language_education");
                    break;
                
                case "qm":
                    $txt = $lng->txt("cd_qm_evaluation");
                    break;
                
                case "fee":
                    $txt = $lng->txt("cd_fee");
                    break;
                
                case "entry_date":
                    $txt = $lng->txt("cd_entry_date");
                    break;
                
                default:
                    $txt = $lng->txt($f);
                    break;
            }
            
            //$ws->writeString(0, $col++, ilExcelUtils::_convert_text($txt));
            $excel->setCell(1, $col++, $txt);
        }
        
        $cnt = 2;
        $all_cnt = 0;
        foreach ($trainers as $t) {
            $all_cnt++;
            $tobj = new ilCDTrainer($t["usr_id"]);

            if (in_array($t["usr_id"], $filter)) {
                continue;
            }

            if ($limit > 0 && $all_cnt > ($limit + $offset)) {
                continue;
            }

            if ($offset > 0 && $all_cnt <= $offset) {
                continue;
            }

            $log->write("Excel write trainer: (" . $cnt . ") " . $t["login"] . " [" . $t["id"] . "]");

            $col = 0;
            foreach ($fields as $f => $def) {
                $val = "";
                switch ($f) {
                    case "username":
                        $val = $t["login"];
                        break;
                    
                    case "center_id":
                        $val = cdCenter::lookupTitle($t[$f]);
                        break;
                    
                    case "cd_sel_country":
                    case "cd_sel_country2":
                        $val = $lng->txt("meta_c_" . $t["sel_country"]);
                        break;
                    
                    case "gender":
                        $val = $lng->txt("gender_" . $t[$f]);
                        break;
                        
                    case "mother_tongue":
                        $lngtxt = array();
                        foreach ($tobj->cd_trainer_mtl as $l) {
                            $lngtxt[] = $lng->txt("meta_l_" . $l);
                        }
                        $val = implode($lngtxt, ";");
                        break;
                    
                    case "instruction_language":
                        $lngtxt = array();
                        foreach ($tobj->cd_trainer_il as $l) {
                            $lngtxt[] = $lng->txt("meta_l_" . $l);
                        }
                        $val = implode($lngtxt, ";");
                        break;
                    
                    case "language_skills":
                        $lngtxt = array();
                        foreach ($tobj->cd_trainer_ls as $l) {
                            $lngtxt[] = $lng->txt("meta_l_" . $l["lang"]) . "-" . $l["slevel"];
                        }
                        $val = implode($lngtxt, ";");
                        break;
                        
                    case "given_lessons":
                        switch ($t[$f]) {
                            case 1: $val = "0-50"; break;
                            case 2: $val = "50-100"; break;
                            case 3: $val = "> 100"; break;
                        }
                        break;
                        
                    case "further_education":
                        $fes = array();
                        foreach ($tobj->cd_trainer_fe as $fe) {
                            $fes[] = $fe["year"] . ": " . $fe["description"];
                        }
                        $val = implode($fes, ";");
                        break;
                        
                    case "teach_exp_tech_lang":
                        $tls = array();
                        include_once("./Services/CD/classes/class.ilCDTechnicalLanguage.php");
                        $tlangs = ilCDTechnicalLanguage::getAll();
                        foreach ($tobj->cd_trainer_tl as $k => $tl) {
                            $v = "";
                            switch ($tl["val"]) {
                                case 1: $v = $lng->txt("none"); break;
                                case 2: $v = $lng->txt("little"); break;
                                case 3: $v = $lng->txt("much"); break;
                            }
                            $tls[] = $a_pl->txt($tlangs[$k]) . " (" . $v . ")";
                        }
                        $val = implode($tls, ";");
                        break;
                    
                    case "teach_type_exp":
                        $tls = array();
                        include_once("./Services/CD/classes/class.ilCDTeachType.php");
                        $ttypes = ilCDTeachType::getAll();
                        foreach ($tobj->cd_trainer_tt as $k => $tt) {
                            $v = "";
                            switch ($tt["val"]) {
                                case 1: $v = $lng->txt("none"); break;
                                case 2: $v = $lng->txt("little"); break;
                                case 3: $v = $lng->txt("much"); break;
                            }
                            $tts[] = $lng->txt($ttypes[$k]) . " (" . $v . ")";
                        }
                        $val = implode($tts, ";");
                        break;
                    
                    case "cefr_knowledge":
                        switch ($t[$f]) {
                            case 1: $val = $lng->txt("none"); break;
                            case 2: $val = $lng->txt("little"); break;
                            case 3: $val = $lng->txt("much"); break;
                        }
                        break;
                        
                    case "cefr_exam_license":
                        $elics = array();
                        
                        foreach ($tobj->cd_trainer_el as $el) {
                            $d = explode("-", $el["end_date"]);
                            $elics[] = $el["license"] . " (" . $d[2] . "." . $d[1] . "." . $d[0] . ")";
                        }
                        $val = implode($elics, ";");
                        break;
                        
                    case "fee":
                        $fee = unserialize($t[$f]);
                        if (is_array($fee) && count($fee) > 0) {
                            $fee = $fee[count($fee) - 1];
                            $f = explode("-", $fee["date"]);
                            $val = $fee["val"] . " (" . $f[2] . "." . $f[1] . "." . $f[0] . ")";
                        }
                        break;


                    default:
                        $val = $t[$f];
                        break;
                }
                $val = str_replace(";", ", ", $val);
                //$ws->writeString($cnt, $col++, ilExcelUtils::_convert_text($val));
                $excel->setCell($cnt, $col++, $val);
            }
            $cnt++;
        }
        $log->write("-- trying to close the wb ");
        //$workbook->close();
        $log->write("-- successfully closed the wb ");
        $exc_name = ilUtil::getASCIIFilename("trainer");

        $log->write("-- deliver the file ");

        //ilUtil::deliverFile($excelfile, $exc_name.".xls", "application/vnd.ms-excel");
        $excel->sendToClient($exc_name);
    }

    /**
     * Is trainer?
     *
     * @param int $a_user_id user id
     * @return bool
     */
    public static function isTrainer($a_user_id)
    {
        global $ilDB;

        $set = $ilDB->query(
            "SELECT id FROM cd_trainer " .
            " WHERE id = " . $ilDB->quote($a_user_id, "integer")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            return true;
        }
        return false;
    }
}
