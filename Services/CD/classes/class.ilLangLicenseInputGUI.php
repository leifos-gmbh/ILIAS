<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/interfaces/interface.ilTableFilterItem.php");
include_once("./Services/Form/classes/class.ilSubEnabledFormPropertyGUI.php");
include_once 'Services/UIComponent/Toolbar/interfaces/interface.ilToolbarItem.php';
include_once("./Services/Form/interfaces/interface.ilMultiValuesItem.php");

/**
* This class represents a selection list property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
* @ingroup	ServicesForm
*/
class ilLangLicenseInputGUI extends ilSubEnabledFormPropertyGUI implements ilTableFilterItem, ilToolbarItem, ilMultiValuesItem
{
    protected $cust_attr = array();
    protected $options = array();
    protected $value;
    
    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);
        $this->setType("lang_lic");
    }

    /**
    * Get Options.
    *
    * @return	array	Options. Array ("value" => "option_text")
    */
    public function getOptions()
    {
        global $lng;
        
        include_once("./Services/CD/classes/class.ilCDLangExamLicense.php");
        $options = array("" => $lng->txt("please_select"));
        foreach (ilCDLangExamLicense::getLicenses() as $k => $v) {
            $options[$k] = $v;
        }
        return $options;
    }
    
    /**
    * Set Value.
    *
    * @param	string	$a_value	Value
    */
    public function setValue($a_value)
    {
        if ($this->getMulti() && is_array($a_value)) {
            $this->setMultiValues($a_value);
            $a_value = array_shift($a_value);
        }
        $this->value = $a_value;
    }

    /**
    * Get Value.
    *
    * @return	string	Value
    */
    public function getValue()
    {
        return $this->value;
    }

    /**
    * Set date values
    *
    * @param	string	$a_value	Value
    */
    public function setDateValues($a_value)
    {
        $this->date_values = $a_value;
    }

    /**
    * Get date values
    *
    * @return	string	Value
    */
    public function getDateValues()
    {
        return $this->date_values;
    }
    
    
    public function setMulti($a_multi, $a_sortable = false, $a_addremove = true)
    {
        $this->multi = (bool) $a_multi;
    }
    
    /**
    * Set value by array
    *
    * @param	array	$a_values	value array
    */
    public function setValueByArray($a_values)
    {
        $this->setValue($a_values[$this->getPostVar()]);
        $this->setDateValues($a_values[$this->getPostVar() . "_d"]);
    }

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        global $lng;

        $valid = true;
        if (!$this->getMulti()) {
            $_POST[$this->getPostVar()] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
            $_POST[$this->getPostVar() . "_d"] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
            if ($this->getRequired() && ($_POST[$this->getPostVar()] == ""
                || $_POST[$this->getPostVar()] . "_d" == "")) {
                $valid = false;
            }
        } else {
            foreach ($_POST[$this->getPostVar()] as $idx => $value) {
                $_POST[$this->getPostVar()][$idx] = ilUtil::stripSlashes($value);
            }
            foreach ($_POST[$this->getPostVar() . "_d"] as $idx => $value) {
                $_POST[$this->getPostVar() . "_d"][$idx] = ilUtil::stripSlashes($value);
            }
            $_POST[$this->getPostVar()] = array_unique($_POST[$this->getPostVar()]);
            $_POST[$this->getPostVar() . "_d"] = array_unique($_POST[$this->getPostVar() . "_d"]);

            if ($this->getRequired() && (!trim(implode("", $_POST[$this->getPostVar()])) || !trim(implode("", $_POST[$this->getPostVar() . "_d"])))) {
                $valid = false;
            }
            
            // check dates format
            foreach ($_POST[$this->getPostVar()] as $k => $v) {
                if ($v != "") {
                    $di = $_POST[$this->getPostVar() . "_d"][$k];
                    $di = explode(".", $di);
                    $dd = (int) $di[0];
                    $mm = (int) $di[1];
                    $yy = (int) $di[2];
                    $dvalid = true;
                    if ($dd < 1 || $dd > 31 || $mm < 1 || $mm > 12 || $yy < 1900 || $yy > 9999) {
                        $this->setAlert($lng->txt("cd_enter_valid_date_format"));
                        return false;
                    }
                }
            }
        }
        if (!$valid) {
            $this->setAlert($lng->txt("msg_input_is_required"));
            return false;
        }
        return $this->checkSubItemsInput();
    }
    
    /**
    * Render item
    */
    public function render($a_mode = "")
    {
        global $lng;
        
        $tpl = new ilTemplate("tpl.prop_lang_lic.html", true, true, "Services/CD");

        // determin value to select. Due to accessibility reasons we
        // should always select a value (per default the first one)
        $first = true;
        foreach ($this->getOptions() as $option_value => $option_text) {
            if ($first) {
                $sel_value = $option_value;
            }
            $first = false;
            if ((string) $option_value == (string) $this->getValue()) {
                $sel_value = $option_value;
            }
        }
        
        // license selection
        foreach ($this->getOptions() as $option_value => $option_text) {
            $tpl->setCurrentBlock("prop_select_option");
            $tpl->setVariable("VAL_SELECT_OPTION", ilUtil::prepareFormOutput($option_value));
            if ((string) $sel_value == (string) $option_value) {
                $tpl->setVariable(
                    "CHK_SEL_OPTION",
                    'selected="selected"'
                );
            }
            $tpl->setVariable("TXT_SELECT_OPTION", $option_text);
            $tpl->parseCurrentBlock();
        }

        //VAL_LD
        if (is_array($this->date_values)) {
            $tpl->setVariable("VAL_LD", $this->date_values[0]);
        }

        $tpl->setVariable("ID", $this->getFieldId());
        
        $postvar = $this->getPostVar();
        $postvar_d = $this->getPostVar() . "_d";
        if ($this->getMulti() && substr($postvar, -2) != "[]") {
            $postvar .= "[]";
            $postvar_d .= "[]";
        }
        
        $tpl->setVariable("POST_VAR", $postvar);
        $tpl->setVariable("POST_VAR_D", $postvar_d);
        $tpl->setVariable("TXT_LICENSE_UNTIL", $lng->txt("license_until"));
        
        // multi icons
        if ($this->getMulti() && !$a_mode && !$this->getDisabled()) {
            $tpl->setVariable("MULTI_ICONS", $this->getMultiIconsHTML());
        }

        // hid = "";
        $dv = $this->getDateValues();

        if ($dv != "") {
            if (!is_array($dv)) {
                $dv = array($dv);
            }
            $n = "ilMultiAddValues~" . $this->getPostVar() . "_d";
            $hid = "<input type='hidden' value='" . implode("~", $dv) . "' name='" . $n . "' id='" . $n . "'/>";
        }
        
        return $tpl->get() . $hid;
    }
    
    /**
    * Insert property html
    *
    * @return	int	Size
    */
    public function insert(&$a_tpl)
    {
        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $this->render());
        $a_tpl->parseCurrentBlock();
    }

    /**
    * Get HTML for table filter
    */
    public function getTableFilterHTML()
    {
        $html = $this->render();
        return $html;
    }

    /**
    * Get HTML for toolbar
    */
    public function getToolbarHTML()
    {
        $html = $this->render("toolbar");
        return $html;
    }
}
