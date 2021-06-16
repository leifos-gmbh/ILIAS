<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Form/classes/class.ilTextInputGUI.php");

/**
* This class represents a text property in a property form.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class ilCDDateInputGUI extends ilTextInputGUI
{
    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "")
    {
        parent::__construct($a_title, $a_postvar);
        $this->setInputType("cddate");
        $this->setSize(11);
        $this->setMaxLength(10);
    }
    
    /**
     * Check input, strip slashes etc. set alert, if input is not ok.
     *
     * @return	boolean		Input ok, true/false
     */
    public function checkInput()
    {
        global $lng;

    
        // check dates format
        $di = $_POST[$this->getPostVar()];
        if ($di != "") {
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
        
        return parent::checkInput();
    }

    
    /**
    * Render item
    */
    public function render($a_mode = "")
    {
        $tpl = new ilTemplate("tpl.prop_cddate.html", true, true, "Services/CD");
        if (strlen($this->getValue())) {
            $tpl->setCurrentBlock("prop_text_propval");
            $tpl->setVariable("PROPERTY_VALUE", ilUtil::prepareFormOutput($this->getValue()));
            $tpl->parseCurrentBlock();
        }

        $tpl->setVariable('PROP_INPUT_TYPE', 'text');
        $tpl->setVariable("ID", $this->getFieldId());
        $tpl->setVariable("SIZE", $this->getSize());
        if ($this->getMaxLength() != null) {
            $tpl->setVariable("MAXLENGTH", $this->getMaxLength());
        }
        
        if ($this->getDisabled()) {
            $tpl->setVariable(
                "DISABLED",
                " disabled=\"disabled\""
            );
            $tpl->setVariable(
                "HIDDEN_INPUT",
                $this->getHiddenTag($this->getPostVar(), $this->getValue())
            );
        } else {
            $tpl->setVariable("POST_VAR", $this->getPostVar());
        }
        
        return $tpl->get();
    }
    
    /**
    * Insert property html
    *
    * @return	int	Size
    */
    public function insert($a_tpl)
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $html);
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
