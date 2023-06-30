<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Company table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class cdCompanyTableGUI extends ilTable2GUI
{
    
    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_pl, $a_user_admin_centers = null)
    {
        global $ilCtrl, $lng, $ilAccess, $lng;

        $this->setId("cdcmpn");
        $this->setShowRowsSelector(true);

        $this->pl = $a_pl;
        $this->pl->includeClass("class.cdCenter.php");
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->initFilter();
        
        $this->pl->includeClass("class.cdCompany.php");
        $this->setData($this->getCompanies($a_user_admin_centers));
        $this->setTitle($this->pl->txt("companies"));

        $this->addColumn("", "", "1");
        //$this->addColumn($this->lng->txt("title"), "title");
        // Firmenname von heller eingepflegt!!! - 29.03.2011
        $this->addColumn($this->pl->txt("company"), "title");
        $this->addColumn($this->pl->txt("city"), "city");
        $this->addColumn($this->pl->txt("creation_user"), "creation_user");
        $this->addColumn($this->pl->txt("center"), "center");
        $this->addColumn($this->pl->txt("nr_users"), "nr_users");
        $this->addColumn($this->lng->txt("actions"), "actions");

        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate($this->pl->getDirectory() . "/templates/tpl.company_row.html");
        $this->setEnableTitle(true);

        $this->setDefaultOrderField("title");
        $this->setDefaultOrderDirection("asc");

        // HELLER 10.09.2013 FIX - Firmen sollen nicht mehr gel�scht werden!!! - Wichtig da Daten verloren/falsch zugeordnet werden
        //$this->addMultiCommand("confirmCompanyDeletion", $lng->txt("delete"));
        //$this->addCommandButton("", $lng->txt(""));
    }
    
    /**
     * Init filter
     */
    public function initFilter()
    {
        global $lng;

        $f = new ilTextInputGUI($lng->txt("name"), "name");
        $this->addFilterItem($f);
        $f->readFromSession();
        $this->filter["name"] = $f->getValue();
    }

    /**
     * Get companies
     *
     * @param
     * @return
     */
    public function getCompanies($a_user_admin_centers = null)
    {
        $comp = cdCompany::getAllCompanies(
            true,
            $a_user_admin_centers,
            $this->filter
        );
        
        $center_name = array();
        foreach ($comp as $k => $c) {
            if (!isset($center_name[$c["center_id"]])) {
                $center_name[$c["center_id"]] =
                    cdCenter::lookupTitle($c["center_id"]);
            }
            $comp[$k]["center"] = $center_name[$c["center_id"]];
        }
        
        return $comp;
    }
    
    
    /**
     * Should this field be sorted numeric?
     *
     * @return	boolean		numeric ordering; default is false
     */
    public function numericOrdering($a_field)
    {
        if (in_array($a_field, array("nr_users"))) {
            return true;
        }
        return false;
    }

    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        global $lng, $ilCtrl;

        
        // parent company
        if ($a_set["parent_company"] > 0) {
            $this->tpl->setCurrentBlock("parent_company");
            $this->tpl->setVariable("PARENT_COMPANY", cdCompany::lookupTitle($a_set["parent_company"]));
            $this->tpl->setVariable("TXT_UNDER", $this->pl->txt("parent_company"));
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("VAL_TITLE", $a_set["title"]);
        $this->tpl->setVariable("VAL_CITY", $a_set["city"]);
        $this->tpl->setVariable("VAL_NR_USERS", $a_set["nr_users"]);
        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        //HELLER CREATION USER
        $name = ilObjUser::_lookupName($a_set["creation_user"]);
        if ($name["login"] != "") {
            $this->tpl->setVariable("VAL_CREATIONUSER", $name["lastname"] . ", " . $name["firstname"] . " [" . $name["login"] . "]");
        }
        $this->tpl->setVariable("VAL_ID", $a_set["id"]);
        $this->tpl->setVariable("VAL_CENTER", $a_set["center"]);
        $this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
        $ilCtrl->setParameter($this->parent_obj, "company_id", $a_set["id"]);
        $this->tpl->setVariable(
            "CMD_EDIT",
            $ilCtrl->getLinkTarget($this->parent_obj, "editCompany")
        );
        $this->tpl->setVariable("TXT_LIST_USERS", $this->pl->txt("list_users"));
        $this->tpl->setVariable(
            "CMD_LIST_USERS",
            $ilCtrl->getLinkTarget($this->parent_obj, "listUsers")
        );
    }
}
