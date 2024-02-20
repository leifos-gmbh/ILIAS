<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Company application class
 *
 * @author Alex Killing <alex.killing>
 * @version $Id$
 */
class cdCompany
{
    /**
     * Constructor
     *
     * @param
     */
    public function __construct($a_id = 0)
    {
        if ($a_id > 0) {
            $this->setId($a_id);
            $this->read();
        }
    }

    /**
     * Set id
     *
     * @param	integer	id
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
     * Set title
     *
     * @param	string	title
     */
    public function setTitle($a_val)
    {
        $this->title = $a_val;
    }

    /**
     * Get title
     *
     * @return	string	title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set center id
     *
     * @param	int	center id
     */
    public function setCenterId($a_val)
    {
        $this->center_id = $a_val;
    }

    /**
     * Get center id
     *
     * @return	int	center id
     */
    public function getCenterId()
    {
        return $this->center_id;
    }

    /**
     * Set parent company
     *
     * @param int $a_val parent company id
     */
    public function setParentCompany($a_val)
    {
        $this->parent_company = $a_val;
    }
    
    /**
     * Get parent company
     *
     * @return int parent company id
     */
    public function getParentCompany()
    {
        return $this->parent_company;
    }
    
    /**
     * Set street
     *
     * @param	string	street
     */
    public function setStreet($a_val)
    {
        $this->street = $a_val;
    }

    /**
     * Get street
     *
     * @return	string	street
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set postal code
     *
     * @param	string	postal code
     */
    public function setPostalCode($a_val)
    {
        $this->postal_code = $a_val;
    }

    /**
     * Get postal code
     *
     * @return	string	postal code
     */
    public function getPostalCode()
    {
        return $this->postal_code;
    }

    /**
     * Set city
     *
     * @param	string	city
     */
    public function setCity($a_val)
    {
        $this->city = $a_val;
    }

    /**
     * Get city
     *
     * @return	string	city
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set country
     *
     * @param	string	country
     */
    public function setCountry($a_val)
    {
        $this->country = $a_val;
    }

    /**
     * Get country
     *
     * @return	string	country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set po box
     *
     * @param	string	po box
     */
    public function setPoBox($a_val)
    {
        $this->po_box = $a_val;
    }

    /**
     * Get po box
     *
     * @return	string	po box
     */
    public function getPoBox()
    {
        return $this->po_box;
    }

    /**
     * Set fax
     *
     * @param	string	fax
     */
    public function setFax($a_val)
    {
        $this->fax = $a_val;
    }

    /**
     * Get fax
     *
     * @return	string	fax
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set contact firstname
     *
     * @param	string	contact firstname
     */
    public function setContactFirstname($a_val)
    {
        $this->contact_firstname = $a_val;
    }

    /**
     * Get contact firstname
     *
     * @return	string	contact firstname
     */
    public function getContactFirstname()
    {
        return $this->contact_firstname;
    }

    /**
     * Set contact lastname
     *
     * @param	string	contact lastname
     */
    public function setContactLastname($a_val)
    {
        $this->contact_lastname = $a_val;
    }

    /**
     * Get contact lastname
     *
     * @return	string	contact lastname
     */
    public function getContactLastname()
    {
        return $this->contact_lastname;
    }

    /**
     * Set contact tel
     *
     * @param	string	contact tel
     */
    public function setContactTel($a_val)
    {
        $this->contact_tel = $a_val;
    }

    /**
     * Get contact tel
     *
     * @return	string	contact tel
     */
    public function getContactTel()
    {
        return $this->contact_tel;
    }

    /**
     * Set company password
     *
     * @param	string	company password
     */
    public function setCompanyPassword($a_val)
    {
        $this->company_password = $a_val;
    }

    /**
     * Get company password
     *
     * @return	string	company password
     */
    public function getCompanyPassword()
    {
        return $this->company_password;
    }

    /**
     * Set internet
     *
     * @param	string	$a_val	internet
     */
    public function setInternet($a_val)
    {
        $this->internet = $a_val;
    }

    /**
     * Get internet
     *
     * @return	string	internet
     */
    public function getInternet()
    {
        return $this->internet;
    }

    /**
     * Set creation user
     *
     * @param int $a_val creation user id
     */
    public function setCreationUser($a_val)
    {
        $this->creation_user = $a_val;
    }
    
    /**
     * Get creation user
     *
     * @return int creation user id
     */
    public function getCreationUser()
    {
        return $this->creation_user;
    }
    
    /**
     * Set title amendment
     *
     * @param string $a_val title amendment
     */
    public function setTitleAmendment($a_val)
    {
        $this->title_amendment = $a_val;
    }
    
    /**
     * Get title amendment
     *
     * @return string title amendment
     */
    public function getTitleAmendment()
    {
        return $this->title_amendment;
    }
    
    /**
     * Set address amendment
     *
     * @param string $a_val address amendment
     */
    public function setAddressAmendment($a_val)
    {
        $this->address_amendment = $a_val;
    }
    
    /**
     * Get address amendment
     *
     * @return string address amendment
     */
    public function getAddressAmendment()
    {
        return $this->address_amendment;
    }
    /**
     * Read
     *
     * @param
     * @return
     */
    public function read()
    {
        global $ilDB;

        $set = $ilDB->query(
            "SELECT * FROM cd_company WHERE " .
        " id = " . $ilDB->quote($this->getId(), "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->setTitle($rec["title"]);
            $this->setCenterId($rec["center_id"]);
            $this->setStreet($rec["street"]);
            $this->setPostalCode($rec["postal_code"]);
            $this->setCity($rec["city"]);
            $this->setCountry($rec["country"]);
            $this->setPoBox($rec["po_box"]);
            $this->setFax($rec["fax"]);
            $this->setContactFirstname($rec["contact_firstname"]);
            $this->setContactLastname($rec["contact_lastname"]);
            $this->setContactTel($rec["contact_tel"]);
            $this->setCompanyPassword($rec["company_password"]);
            $this->setInternet($rec["internet"]);
            $this->setParentCompany($rec["parent_company"]);
            $this->setCreationUser($rec["creation_user"]);
            $this->setTitleAmendment($rec["title_amendment"]);
            $this->setAddressAmendment($rec["address_amendment"]);
        }
    }

    /**
     * Create company
     */
    public function create()
    {
        global $ilDB;

        $this->setId($ilDB->nextId("cd_company"));

        /*$ilDB->manipulate("INSERT INTO cd_company ".
            "(id, title, center_id, street, postal_code, city, country, po_box,".
            "fax, contact_firstname, contact_lastname, contact_tel, internet, company_password, parent_company) VALUES (".
            $ilDB->quote($this->getId(), "integer").",".
            $ilDB->quote($this->getTitle(), "text").",".
            $ilDB->quote($this->getCenterId(), "integer").",".
            $ilDB->quote($this->getStreet(), "text").",".
            $ilDB->quote($this->getPostalCode(), "text").",".
            $ilDB->quote($this->getCity(), "text").",".
            $ilDB->quote($this->getCountry(), "text").",".
            $ilDB->quote($this->getPoBox(), "text").",".
            $ilDB->quote($this->getFax(), "text").",".
            $ilDB->quote($this->getContactFirstname(), "text").",".
            $ilDB->quote($this->getContactLastname(), "text").",".
            $ilDB->quote($this->getContactTel(), "text").",".
            $ilDB->quote($this->getInternet(), "text").",".
            $ilDB->quote($this->getCompanyPassword(), "text").",".
            $ilDB->quote($this->getParentCompany(), "integer").
            ")");*/
        
        $ilDB->insert("cd_company", array(
            "id" => array("integer", $this->getId()),
            "title" => array("text", $this->getTitle()),
            "center_id" => array("integer", $this->getCenterId()),
            "street" => array("text", $this->getStreet()),
            "postal_code" => array("text", $this->getPostalCode()),
            "city" => array("text", $this->getCity()),
            "country" => array("text", $this->getCountry()),
            "po_box" => array("text", $this->getPoBox()),
            "fax" => array("text", $this->getFax()),
            "contact_firstname" => array("text", $this->getContactFirstname()),
            "contact_lastname" => array("text", $this->getContactLastname()),
            "contact_tel" => array("text", $this->getContactTel()),
            "internet" => array("text", $this->getInternet()),
            "company_password" => array("text", $this->getCompanyPassword()),
            "parent_company" => array("integer", $this->getParentCompany()),
            "creation_user" => array("integer", $this->getCreationUser()),
            "title_amendment" => array("clob", $this->getTitleAmendment()),
            "address_amendment" => array("clob", $this->getAddressAmendment())
            ));
    }

    /**
     * Update company
     */
    public function update()
    {
        global $ilDB;

        /*$ilDB->manipulate("UPDATE cd_company SET ".
            " title = ".$ilDB->quote($this->getTitle(), "text").", ".
            " center_id = ".$ilDB->quote($this->getCenterId(), "integer").", ".
            " street = ".$ilDB->quote($this->getStreet(), "text").", ".
            " postal_code = ".$ilDB->quote($this->getPostalCode(), "text").", ".
            " city = ".$ilDB->quote($this->getCity(), "text").", ".
            " country = ".$ilDB->quote($this->getCountry(), "text").", ".
            " po_box = ".$ilDB->quote($this->getPoBox(), "text").", ".
            " fax = ".$ilDB->quote($this->getFax(), "text").", ".
            " contact_firstname = ".$ilDB->quote($this->getContactFirstname(), "text").", ".
            " contact_lastname = ".$ilDB->quote($this->getContactLastname(), "text").", ".
            " contact_tel = ".$ilDB->quote($this->getContactTel(), "text").", ".
            " internet = ".$ilDB->quote($this->getInternet(), "text").", ".
            " company_password = ".$ilDB->quote($this->getCompanyPassword(), "text").", ".
            " parent_company = ".$ilDB->quote($this->getParentCompany(), "integer").
            " WHERE id = ".$ilDB->quote($this->getId(), "integer")
            );*/
        
        $ilDB->update("cd_company", array(
            "title" => array("text", $this->getTitle()),
            "center_id" => array("integer", $this->getCenterId()),
            "street" => array("text", $this->getStreet()),
            "postal_code" => array("text", $this->getPostalCode()),
            "city" => array("text", $this->getCity()),
            "country" => array("text", $this->getCountry()),
            "po_box" => array("text", $this->getPoBox()),
            "fax" => array("text", $this->getFax()),
            "contact_firstname" => array("text", $this->getContactFirstname()),
            "contact_lastname" => array("text", $this->getContactLastname()),
            "contact_tel" => array("text", $this->getContactTel()),
            "internet" => array("text", $this->getInternet()),
            "company_password" => array("text", $this->getCompanyPassword()),
            "parent_company" => array("integer", $this->getParentCompany()),
            "creation_user" => array("integer", $this->getCreationUser()),
            "title_amendment" => array("clob", $this->getTitleAmendment()),
            "address_amendment" => array("clob", $this->getAddressAmendment())
            ), array(
            "id" => array("integer", $this->getId())
            ));
    }

    /**
     * Delete company
     */
    public function delete()
    {
        global $ilDB;

        $ilDB->manipulate(
            "DELETE FROM cd_company WHERE "
            . " id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    /**
     * Get all companies
     */
    public static function getAllCompanies(
        $a_incl_nr_users = false,
        $a_centers_limit = null,
        $a_filter = ""
    ) {
        global $ilDB;

        $where = "";
        $and = "WHERE ";
        if (is_array($a_centers_limit)) {
            $where = "WHERE " . $ilDB->in("center_id", $a_centers_limit, false, "integer");
            $and = "AND ";
        }
        
        if (isset($a_filter["name"]) && $a_filter["name"] != "") {
            $where .= $and . " LOWER(title) LIKE " . $ilDB->quote("%" . strtolower($a_filter["name"]) . "%", "text");
        }

        $set = $ilDB->query("SELECT * FROM cd_company " . $where . " ORDER BY title");

        $company = array();
        $company_ids = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $company_ids[$rec["id"]] = $rec["id"];
            $company[] = $rec;
        }
        if ($a_incl_nr_users) {
            $nr_of_users = cdCompany::lookupNrOfUsers($company_ids);
            foreach ($company as $k => $v) {
                $company[$k]["nr_users"] = (int) $nr_of_users[$v["id"]];
            }
        }

        return $company;
    }

    /**
     * Lookup property
     *
     * @param	int		level id
     * @return	mixed	property value
     */
    protected static function lookupProperty($a_id, $a_prop)
    {
        global $ilDB;

        $set = $ilDB->query(
            "SELECT $a_prop FROM cd_company WHERE " .
            " id = " . $ilDB->quote($a_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return $rec[$a_prop];
    }

    /**
     * Lookup title
     *
     * @param
     * @return
     */
    public static function lookupTitle($a_id)
    {
        return cdCompany::lookupProperty($a_id, "title");
    }

    /**
     * Lookup center id
     *
     * @param
     * @return
     */
    public static function lookupCenterId($a_id)
    {
        return cdCompany::lookupProperty($a_id, "center_id");
    }

    /**
     * Check wether password is unique
     *
     * @param	string		password
     * @param	int			company id
     * @return	boolean		password unique true/false
     */
    public static function checkUniquePassword($a_pw, $a_id = 0)
    {
        global $ilDB;

        $set = $ilDB->query(
            "SELECT id FROM cd_company WHERE " .
            " company_password = " . $ilDB->quote($a_pw, "text")
        );
        $unique = true;
        while ($rec = $ilDB->fetchAssoc($set)) {
            if ($rec["id"] != $a_id) {
                $unique = false;
            }
        }
        return $unique;
    }

    /**
     * Get company id for password
     *
     * @param
     * @return
     */
    public static function getCompanyIdForPassword($a_pw)
    {
        global $ilDB;

        $set = $ilDB->query(
            "SELECT * FROM cd_company WHERE " .
            " company_password = " . $ilDB->quote($a_pw, "text")
        );
        if ($rec = $ilDB->fetchAssoc($set)) {
            return (int) $rec["id"];
        }

        return 0;
    }

    /**
     * Lookup nr of users
     *
     * @param array $a_comp_ids
     * @return array
     */
    public static function lookupNrOfUsers($a_comp_ids)
    {
        global $ilDB;

        $set = $ilDB->query(
            "SELECT company_id, count(usr_id) cnt FROM usr_data WHERE " .
            $ilDB->in("company_id", $a_comp_ids, false, "integer") .
            " GROUP BY company_id"
        );

        $nrs = [];
        while ($rec = $ilDB->fetchAssoc($set)) {
            $nrs[$rec["company_id"]] = $rec["cnt"];
        }
        return $nrs;
    }

    /**
     * Export learning data as excel
     */
    public function exportExcel($a_pl, $a_users = null)
    {
        global $lng;
        
        $a_pl->includeClass("class.cdNeedsAnalysis.php");

        $ud = ilUserQuery::getUserListData(
            "lastname",
            "asc",
            0,
            999999,
            "",
            "",
            null,
            "",
            "",
            "",
            "",
            null,
            array("street", "country", "city", "gender", "department", "branch",
                "profession", "field_of_responsibility", "zipcode", "phone_office",
                "fax"),
            null,
            "",
            null,
            $this->getId()
        );
        $user_data = array();
        foreach ($ud["set"] as $u) {
            if (!is_array($a_users) || in_array($u["usr_id"], $a_users)) {
                $user_data[] = $u;
            }
        }
        
        $excel = new ilExcel();
        $excel->addSheet("Personendaten");
        
        //
        // user data
        //
        
        // header row
        $col = 0;
        $excel->setCell(1, $col++, $lng->txt("lastname"));
        $excel->setCell(1, $col++, $lng->txt("firstname"));
        $excel->setCell(1, $col++, $lng->txt("login"));
        $excel->setCell(1, $col++, $lng->txt("gender"));
        $excel->setCell(1, $col++, $lng->txt("department"));
        $excel->setCell(1, $col++, $lng->txt("branch"));
        $excel->setCell(1, $col++, $lng->txt("profession"));
        $excel->setCell(1, $col++, $lng->txt("field_of_responsibility"));
        $excel->setCell(1, $col++, $lng->txt("zipcode"));
        $excel->setCell(1, $col++, $lng->txt("city"));
        $excel->setCell(1, $col++, $lng->txt("country"));
        $excel->setCell(1, $col++, $lng->txt("phone_office"));
        $excel->setCell(1, $col++, $lng->txt("fax"));
        $excel->setCell(1, $col++, $lng->txt("email"));
        
        $cnt = 2;
        foreach ($user_data as $user) {
            $col = 0;
            $excel->setCell($cnt, $col++, $user["lastname"]);
            $excel->setCell($cnt, $col++, $user["firstname"]);
            $excel->setCell($cnt, $col++, $user["login"]);
            $excel->setCell($cnt, $col++, $user["gender"]);
            $excel->setCell($cnt, $col++, $user["department"]);
            $excel->setCell($cnt, $col++, $user["branch"]);
            $excel->setCell($cnt, $col++, $user["profession"]);
            $excel->setCell($cnt, $col++, $user["field_of_responsibility"]);
            $excel->setCell($cnt, $col++, $user["zipcode"]);
            $excel->setCell($cnt, $col++, $user["city"]);
            $excel->setCell($cnt, $col++, $user["country"]);
            $excel->setCell($cnt, $col++, $user["phone_office"]);
            $excel->setCell($cnt, $col++, $user["fax"]);
            $excel->setCell($cnt, $col++, $user["email"]);
            $cnt++;
        }
        
        //
        // overview
        //
        $excel->addSheet("Übersicht");
        
        // header row
        $excel->setCell(1, 0, $lng->txt("lastname"));
        $excel->setCell(1, 1, $lng->txt("firstname"));
        $excel->setCell(1, 2, $lng->txt("login"));
        $excel->setCell(1, 3, $a_pl->txt("mother_tongue"));

        $excel->setCell(1, 4, $a_pl->txt("pointscdc"));
        $excel->setCell(1, 5, $a_pl->txt("datumtest"));

        $excel->setCell(1, 6, $a_pl->txt("train_lang"));
        $excel->setCell(1, 7, $a_pl->txt("avg_self"));
        $excel->setCell(1, 8, $a_pl->txt("online"));
        $excel->setCell(1, 9, $a_pl->txt("oral"));
        $excel->setCell(1, 10, $a_pl->txt("target"));
        $excel->setCell(1, 11, $a_pl->txt("tech_lang"));
        $excel->setCell(1, 12, $a_pl->txt("type_of_training"));
        
        // data rows
        $cnt = 2;
        foreach ($user_data as $user) {
            $data = $this->getOverviewData($a_pl, $user["usr_id"]);
            
            foreach ($data["langs"] as $l) {
                $excel->setCell($cnt, 0, $user["lastname"]);
                $excel->setCell($cnt, 1, $user["firstname"]);
                $excel->setCell($cnt, 2, $user["login"]);
                
                $excel->setCell($cnt, 4, $data["points"][$l]);
                $excel->setCell($cnt, 5, $data["ts"][$l]);
                
                $excel->setCell($cnt, 6, $lng->txt("meta_l_" . $l));
                $excel->setCell($cnt, 7, $data["self_avg"][$l]);
                $excel->setCell($cnt, 8, $data["test"][$l]);
                $excel->setCell($cnt, 9, $data["oral_target"][$l]["oral"]);
                $excel->setCell($cnt, 10, $data["oral_target"][$l]["target"]);
                $excel->setCell($cnt, 11, $data["tech_lang"][$l]);
                
                $cnt++;
            }
        }

        //
        // needs analysis
        //
        $excel->addSheet("Bedarfsanalyse");
        
        // header row
        $col = 0;
        $excel->setCell(1, $col++, $lng->txt("lastname"));
        $excel->setCell(1, $col++, $lng->txt("firstname"));
        $excel->setCell(1, $col++, $lng->txt("login"));
        $excel->setCell(1, $col++, $a_pl->txt("course_lang"));
        $excel->setCell(1, $col++, $a_pl->txt("last_course"));
        $excel->setCell(1, $col++, $a_pl->txt("lang_level"));
        if (cdUtil::isDAF()) {
            $a_pl->includeClass("class.cdNeedsAnalysisDAF.php");
            foreach (cdNeedsAnalysisDAF::getQuestions($a_pl) as $k => $question) {
                $excel->setCell(1, $col++, $question);
            }
        } else {
            foreach (cdNeedsAnalysis::$talk_understand as $k => $l) {
                $excel->setCell(1, $col++, $a_pl->txt($l));
            }
            foreach (cdNeedsAnalysis::$write_read as $k => $l) {
                $excel->setCell(1, $col++, $a_pl->txt($l));
            }
            $excel->setCell(1, $col++, $a_pl->txt("technical_language"));
            $excel->setCell(1, $col++, $a_pl->txt("other_technical_language"));
        }
        
        // data rows
        $cnt = 2;
        reset($user_data);
        foreach ($user_data as $user) {
            if (cdUtil::isDAF()) {
                $nas = cdNeedsAnalysisDAF::getNeedsAnalysesOfUser($user["usr_id"]);
            } else {
                $nas = cdNeedsAnalysis::getNeedsAnalysesOfUser($user["usr_id"]);
            }
            foreach ($nas as $na) {
                $col = 0;
                $excel->setCell($cnt, $col++, $user["lastname"]);
                $excel->setCell($cnt, $col++, $user["firstname"]);
                $excel->setCell($cnt, $col++, $user["login"]);
                $excel->setCell($cnt, $col++, $lng->txt("meta_l_" . $na["course_lang"]));
                if (!(int) $na["last_course"]) {
                    $v = $a_pl->txt("option_never");
                } else {
                    $v = $na["last_course"];
                }
                $excel->setCell($cnt, $col++, $v);
                if ($na["lang_level"] == "") {
                    $v = $a_pl->txt("option_none");
                } else {
                    $v = $na["lang_level"];
                }
                $excel->setCell($cnt, $col++, $v);
                
                if (cdUtil::isDAF()) {
                    foreach (cdNeedsAnalysisDAF::getQuestions($a_pl) as $k => $question) {
                        $excel->setCell($cnt, $col++, $na["daf"][$k]["answer"]);
                    }
                } else {
                    $ta = unserialize($na["talk_understanding"]);
                    foreach (cdNeedsAnalysis::$talk_understand as $k => $l) {
                        if ($ta[$k] === 0) {
                            $v = $a_pl->txt("option_never");
                        } else {
                            $v = $a_pl->txt("freq_" . $ta[$k]);
                        }
                        $excel->setCell($cnt, $col++, $v);
                    }
                    $wr = unserialize($na["write_read"]);
                    foreach (cdNeedsAnalysis::$write_read as $k => $l) {
                        if ($wr[$k] === 0) {
                            $v = $a_pl->txt("option_never");
                        } else {
                            $v = $a_pl->txt("freq_" . $wr[$k]);
                        }
                        $excel->setCell($cnt, $col++, $v);
                    }

                    $tl = cdNeedsAnalysis::$technical_lang;
                    if (!(int) $na["tech_lang_sel"]) {
                        $v = $a_pl->txt("option_none");
                    } else {
                        $v = $a_pl->txt($tl[$na["tech_lang_sel"]]);
                    }
                    $excel->setCell($cnt, $col++, $v);
                    $excel->setCell($cnt, $col++, $na["tech_lang_free"]);
                }
                $cnt++;
            }
        }

        //
        // self evaluation
        //
        $excel->addSheet("Selbsteinschätzung");

        
        // header row
        $col = 0;
        $excel->setCell(1, $col++, $lng->txt("lastname"));
        $excel->setCell(1, $col++, $lng->txt("firstname"));
        $excel->setCell(1, $col++, $lng->txt("login"));
        
        // data rows
        $cnt = 2;
        reset($user_data);
        foreach ($user_data as $user) {
            $ses = ilSkillSelfEvaluation::getAllSelfEvaluationsOfUser($user["usr_id"]);
            foreach ($ses as $se) {
                $col = 0;
                $excel->setCell($cnt, $col++, $user["lastname"]);
                $excel->setCell($cnt, $col++, $user["firstname"]);
                $excel->setCell($cnt, $col++, $user["login"]);

                $se = new ilSkillSelfEvaluation($se["id"]);
                $levels = $se->getLevels();
                $stree = new ilSkillTree();
        
                if ($stree->isInTree($se->getTopSkillId())) {
                    $cnode = $stree->getNodeData($se->getTopSkillId());
                    $childs = $stree->getSubTree($cnode);
                    foreach ($childs as $child) {
                        if ($child["type"] == "skll") {
                            // build title
                            $path = $stree->getPathFull($child["child"]);
                            $title = $sep = "";
                            foreach ($path as $p) {
                                if ($p["type"] != "skrt") {
                                    $title .= $sep . $p["title"];
                                    $sep = " > ";
                                }
                            }
        
                            $sk = new ilBasicSkill($child["child"]);
                            $ls = $sk->getLevelData();
        
                            $excel->setCell($cnt, 3, $title);
                            foreach ($ls as $ld) {
                                if ($ld["id"] == $levels[$child["child"]]) {
                                    $excel->setCell($cnt, 4, $ld["title"]);
                                }
                            }
                            $cnt++;
                        }
                    }
                }
                $cnt++;
            }
        }

        //
        // tests
        //
        $excel->addSheet("Tests");

        $a_pl->includeClass("class.cdEntryLevelTest.php");
        
        $fields = array(
            "target_lang" => "language",
            "duration" => "duration",
            "finished" => "finished",
            "points" => "points",
            "ext_level" => "pointscdc",
            "ts" => "datumtest",
            "result_level" => "achieved_level",
            "result_grammar" => "grammar",
            "result_voca" => "voca",
            "result_read" => "read",
            "result_listen" => "listen"
            );

        
        // header row
        $col = 0;
        $excel->setCell(1, $col++, $lng->txt("lastname"));
        $excel->setCell(1, $col++, $lng->txt("firstname"));
        $excel->setCell(1, $col++, $lng->txt("login"));
        foreach ($fields as $f => $l) {
            $excel->setCell(1, $col++, $a_pl->txt($l));
        }
        
        // data rows
        $cnt = 2;
        reset($user_data);
        foreach ($user_data as $user) {
            $ts = cdEntryLevelTest::getEntryLevelTestsOfUser($user["usr_id"]);
            foreach ($ts as $t) {
                $col = 0;
                $excel->setCell($cnt, $col++, $user["lastname"]);
                $excel->setCell($cnt, $col++, $user["firstname"]);
                $excel->setCell($cnt, $col++, $user["login"]);
                
                foreach ($fields as $f => $l) {
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
                                $v = $a_pl->txt("yes");
                            } else {
                                $v = $a_pl->txt("no");
                            }
                            break;
        
                        default:
                            $v = $t[$f];
                            break;
                    }

                    $excel->setCell($cnt, $col++, $v);
                }
                $cnt++;
            }
        }

        $exc_name = ilUtil::getASCIIFilename(preg_replace("/\s/", "_", $this->getTitle()));
        $excel->sendToClient($exc_name);
    }
    
    /**
     * Get overview data for user
     *
     * @param
     * @return
     */
    public function getOverviewData($a_pl, $a_user_id)
    {
        global $lng;
        
        $lng->loadLanguageModule("meta");
        $a_pl->includeClass("class.cdNeedsAnalysis.php");
        $a_pl->includeClass("class.cdEntryLevelTest.php");
        $a_pl->includeClass("class.cdOralTarget.php");
        
        // get needs analysis
        $nas = cdNeedsAnalysis::getNeedsAnalysesOfUser($a_user_id, true);
        $langs = [];
        $tl_str = cdNeedsAnalysis::$technical_lang;
        $tech_lang = [];
        $self_avg = [];
        foreach ($nas as $na) {
            $langs[$na["course_lang"]] = $na["course_lang"];
            $tl = $sep = "";
            if ($na["tech_lang_sel"] != "") {
                $tl = $a_pl->txt($tl_str[$na["tech_lang_sel"]]);
                $sep = ", ";
            }
            if ($na["tech_lang_free"] != "") {
                $tl .= $sep . $na["tech_lang_free"];
            }
            $tech_lang[$na["course_lang"]] = $tl;
        }

        // get self evaluation
        $ses = ilSkillSelfEvaluation::getAllSelfEvaluationsOfUser($a_user_id, true);
        foreach ($ses as $se) {
            $avg = ilSkillSelfEvaluation::getAverageLevel(
                $se["id"],
                $a_user_id,
                $se["top_skill_id"]
            );
            if ($avg != null) {
                $a_pl->includeClass("class.cdLang.php");
                foreach (cdLang::getLanguages() as $l) {
                    //if ($lng->txt("meta_l_".$l) == $avg["skill_title"])
                    if ($lng->txtlng("meta", "meta_l_" . $l, "de") == $avg["skill_title"]) {
                        $self_avg[$l] = $avg["avg_title"];
                        $langs[$l] = $l;
                    }
                }
            }
        }

        // entry level tests
        $ts = cdEntryLevelTest::getEntryLevelTestsOfUser($a_user_id, true);
        foreach ($ts as $t) {
            if ($t["result_level"] != "") {
                $test[$t["target_lang"]] = $t["result_level"];
                //$test[$t["target_lang"]] = $t["ext_level"];
                //$points[$t["target_lang"]] = $t["points"];
                $points[$t["target_lang"]] = $t["ext_level"];
                $ts[$t["target_lang"]] = $t["ts"];
                $langs[$t["target_lang"]] = $t["target_lang"];
            }
        }
        
        // oral/target mark
        $ots = cdOralTarget::readUserValues($a_user_id, true);
        foreach ($ots as $k => $ot) {
            $langs[$k] = $k;
        }

        return array("langs" => $langs, "tech_lang" => $tech_lang,
            "self_avg" => $self_avg, "test" => $test, "points" => $points, "ts" => $ts, "oral_target" => $ots);
    }
    
    /**
     * Get possible parent companies for another company
     *
     * @param
     * @return
     */
    public static function getPossibleParentCompaniesForCenter($a_company_id)
    {
        global $ilDB;
        
        $center_id = cdCompany::lookupCenterId($a_company_id);
        
        $set = $ilDB->query(
            $q = "SELECT * FROM cd_company " .
            " WHERE center_id = " . $ilDB->quote($center_id, "integer") .
            " AND parent_company = " . $ilDB->quote(0, "integer") .
            " AND id <> " . $ilDB->quote($a_company_id, "integer") .
            " ORDER BY title"
        );
        $companies = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $companies[] = $rec;
        }
        
        return $companies;
    }
    
    /**
     * Get child companies
     *
     * @param int $a_company_id company id
     * @return array array of child companies
     */
    public static function getChildCompanies($a_company_id)
    {
        global $ilDB;
        
        $set = $ilDB->query(
            "SELECT * FROM cd_company " .
            " WHERE parent_company = " . $ilDB->quote($a_company_id, "integer") .
            " ORDER BY title"
        );
        $cc = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $cc[$rec["id"]] = $rec;
        }
        return $cc;
    }
}
