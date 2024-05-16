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
class ilLangSkillInputGUI extends ilSubEnabledFormPropertyGUI implements ilTableFilterItem, ilToolbarItem, ilMultiValuesItem
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
        $this->setType("lang_skill");
    }

    /**
    * Get Options.
    *
    * @return	array	Options. Array ("value" => "option_text")
    */
    public function getOptions()
    {
        global $lng;
        
        include_once("./Services/MetaData/classes/class.ilMDLanguageItem.php");
        $tmp_options[""] = $lng->txt("please_select");
        foreach (ilMDLanguageItem::_getPossibleLanguageCodes() as $code) {
            $tmp_options[$code] = $lng->txt('meta_l_' . $code);
        }
        asort($tmp_options, SORT_STRING);

        return $this->options = $tmp_options;
    }
    
    /**
     * Get level options
     *
     * @param
     * @return
     */
    public function getLevelOptions()
    {
        global $lng;
        
        return array("" => $lng->txt("please_select"),
            "A1" => "A1", "A2" => "A2",
            "B1" => "B1", "B2" => "B2",
            "C1" => "C1", "C2" => "C2");
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
    * Set Values
    *
    * @param	string	$a_value	Value
    */
    public function setLevelValues($a_value)
    {
        $this->level_values = $a_value;
    }

    /**
     * Get Value.
     *
     * @return	string	Value
     */
    public function getLevelValues()
    {
        return $this->level_values;
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
        $this->setLevelValues($a_values[$this->getPostVar() . "_l"]);
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
            $_POST[$this->getPostVar() . "_l"] = ilUtil::stripSlashes($_POST[$this->getPostVar()]);
            if ($this->getRequired() && ($_POST[$this->getPostVar()] == ""
                || $_POST[$this->getPostVar()] . "_l" == "")) {
                $valid = false;
            }
        } else {
            foreach ($_POST[$this->getPostVar()] as $idx => $value) {
                $_POST[$this->getPostVar()][$idx] = ilUtil::stripSlashes($value);
            }
            foreach ($_POST[$this->getPostVar() . "_l"] as $idx => $value) {
                $_POST[$this->getPostVar() . "_l"][$idx] = ilUtil::stripSlashes($value);
            }
            $_POST[$this->getPostVar()] = array_unique($_POST[$this->getPostVar()]);
            $_POST[$this->getPostVar() . "_l"] = array_unique($_POST[$this->getPostVar() . "_l"]);

            if ($this->getRequired() && (!trim(implode("", $_POST[$this->getPostVar()])) || !trim(implode("", $_POST[$this->getPostVar() . "_l"])))) {
                $valid = false;
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
        $tpl = new ilTemplate("tpl.prop_lang_skill.html", true, true, "Services/CD");

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
        
        // language selection
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
        
        // level selection
        $lvs = $this->getLevelValues();
        $sel_lv = (is_array($lvs))
            ? $lvs[0]
            : "";
        foreach ($this->getLevelOptions() as $option_value => $option_text) {
            $tpl->setCurrentBlock("prop_select_option_l");
            $tpl->setVariable("VAL_SELECT_OPTION_L", ilUtil::prepareFormOutput($option_value));
            if ((string) $sel_lv == (string) $option_value) {
                // this will be set by JS and JS fails (at least under Chrome) if we already set selected here...
                //$tpl->setVariable("CHK_SEL_OPTION_L",
                //	'selected="selected"');
            }
            $tpl->setVariable("TXT_SELECT_OPTION_L", $option_text);
            $tpl->parseCurrentBlock();
        }

        
        
        $tpl->setVariable("ID", $this->getFieldId());
        
        $postvar = $this->getPostVar();
        $postvar_l = $this->getPostVar() . "_l";
        
        if ($this->getMulti() && substr($postvar, -2) != "[]") {
            $postvar .= "[]";
            $postvar_l .= "[]";
        }
        
        $tpl->setVariable("POST_VAR", $postvar);
        $tpl->setVariable("POST_VAR_L", $postvar_l);
        
        // multi icons
        if ($this->getMulti() && !$a_mode && !$this->getDisabled()) {
            $tpl->setVariable("MULTI_ICONS", $this->getMultiIconsHTML());
        }

        // hid = "";
        $lv = $this->getLevelValues();

        if ($lv != "") {
            if (!is_array($lv)) {
                $lv = array($lv);
            }
            $n = "ilMultiAddValues~" . $this->getPostVar() . "_l";
            $hid = "<input type='hidden' value='" . implode("~", $lv) . "' name='" . $n . "' id='" . $n . "'/>";
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
