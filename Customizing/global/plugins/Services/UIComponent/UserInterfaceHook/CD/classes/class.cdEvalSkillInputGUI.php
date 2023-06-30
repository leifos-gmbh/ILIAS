<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This class represents a skill evaluation matrix in the participant evaluation form.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*/
class cdEvalSkillInputGUI extends ilFormPropertyGUI
{
    protected $value = "1";
    protected $checked;
    protected $optiontitle = "";
    protected $additional_attributes = '';
    
    /**
    * Constructor
    *
    * @param	string	$a_title	Title
    * @param	string	$a_postvar	Post Variable
    */
    public function __construct($a_title = "", $a_postvar = "", $a_pl = null)
    {
        parent::__construct($a_title, $a_postvar);
        $this->setType("cdskills");
        $this->pl = $a_pl;
    }

    /**
     * Set Value.
     *
     * @param	string	$a_value	Value
     */
    public function setValue($a_value)
    {
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
    * Set value by array
    *
    * @param	object	$a_item		Item
    */
    public function setValueByArray($a_values)
    {
        $this->setValue($a_values[$this->getPostVar()]);
    }
    

    /**
    * Check input, strip slashes etc. set alert, if input is not ok.
    *
    * @return	boolean		Input ok, true/false
    */
    public function checkInput()
    {
        global $lng;
        
        //		$_POST[$this->getPostVar()] =
        //			ilUtil::stripSlashes($_POST[$this->getPostVar()]);

        // only not ok, if checkbox not checked
        //		if (!$ok && $_POST[$this->getPostVar()] == "")
        //		{
        $ok = true;
        //		}

        return $ok;
    }
    
    /**
     * Get scale values
     */
    public function getScaleValues()
    {
        $this->pl->includeClass("class.cdParticipantEvaluation.php");
        return cdParticipantEvaluation::getSkillScaleValues($this->pl);
    }
    
    /**
     * Get skills
     */
    public function getSkills()
    {
        return array(
            "listening" => $this->pl->txt("listening"),
            "speaking" => $this->pl->txt("speaking"),
            "reading" => $this->pl->txt("reading"),
            "fluency" => $this->pl->txt("language_fluency"),
            "writing" => $this->pl->txt("writing"),
            "grammar" => $this->pl->txt("grammar"),
            "communication" => $this->pl->txt("business_communication")
            );
    }
    
    /**
    * Render item
    */
    public function render($a_mode = '')
    {
        $tpl = $this->pl->getTemplate("tpl.pe_skill_eval_input.html");

        $cval = $this->getValue();

        // header cells
        foreach ($this->getScaleValues() as $val => $scale_txt) {
            $tpl->setCurrentBlock("head_cell");
            $tpl->setVariable("SCALE_VAL_TXT", $scale_txt);
            $tpl->parseCurrentBlock();
        }
        
        // skill rows
        $cnt = 0;
        foreach ($this->getSkills() as $skill => $skill_txt) {
            $cnt++;
            foreach ($this->getScaleValues() as $val => $scale_txt) {
                $tpl->setCurrentBlock("cell");
                if (isset($cval[$skill]) && $cval[$skill] == $val) {
                    $tpl->setVariable("SELECTED", ' checked="checked" ');
                }
                $tpl->setVariable("SKILL_VAL", $skill);
                $tpl->setVariable("VAL", $val);
                $tpl->setVariable("POST_VAR", $this->getPostVar());
                $tpl->parseCurrentBlock();
            }
            
            if (($cnt % 2) == 1) {
                $tpl->touchBlock("zebra");
            }
            
            $tpl->setCurrentBlock("row");
            $tpl->setVariable("SKILL_TXT", $skill_txt);
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    /**
    * Insert property html
    *
    * @return	int	Size
    */
    public function insert(&$a_tpl)
    {
        $html = $this->render();

        $a_tpl->setCurrentBlock("prop_generic");
        $a_tpl->setVariable("PROP_GENERIC", $html);
        $a_tpl->parseCurrentBlock();
    }
}
