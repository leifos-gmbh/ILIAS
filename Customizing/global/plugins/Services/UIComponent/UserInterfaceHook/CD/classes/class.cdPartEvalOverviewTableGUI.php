<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * TableGUI class for participant evaluation
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class cdPartEvalOverviewTableGUI extends ilTable2GUI
{
    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_pl, $a_user_id)
    {
        global $ilCtrl, $lng;

        $this->pl = $a_pl;
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->pl->includeClass("class.cdParticipantEvaluation.php");
        $data = cdParticipantEvaluation::getAllParticipantEvaluations(array("user_id" => $a_user_id));
        $this->setData($data);
        $this->setTitle($this->pl->txt("part_eval_overview") . ": " . ilUserUtil::getNamePresentation($a_user_id, false, false, "", true));

        $this->setDefaultOrderField("creation_date");
        $this->setDefaultOrderDirection("desc");

        $this->addColumn("", "1", "", true);

        $this->all_cols = array(
            "created_on" => array(
                "txt" => "created_on"
            ),
            "last_update" => array(
                "txt" => "last_update"
            ),
            "published" => array(
                "txt" => "published"
            ),
            "course" => array(
                "txt" => "course"
            ),
            "course_level" => array(
                "txt" => "course_level"
            ),
            "after_x_lessons" => array(
                "txt" => "after_x_lessons_short"
            ),
            "starting_cef" => array(
                "txt" => "starting_cef_short"
            ),
            "listening" => array(
                "txt" => "listening",
                "skill" => true
            ),
            "speaking" => array(
                "txt" => "speaking",
                "skill" => true
            ),
            "reading" => array(
                "txt" => "reading",
                "skill" => true
            ),
            "fluency" => array(
                "txt" => "language_fluency",
                "skill" => true
            ),
            "writing" => array(
                "txt" => "writing",
                "skill" => true
            ),
            "grammar" => array(
                "txt" => "grammar",
                "skill" => true
            ),
            "communication" => array(
                "txt" => "business_communication_short",
                "skill" => true
            ),
            "current_cef" => array(
                "txt" => "current_cef_short"
            ),
            "test_level" => array(
                "txt" => "test_level"
            ),
            "test_attendance" => array(
                "txt" => "test_attendance"
            ),
            "test_result" => array(
                "txt" => "result"
            ),
            "stay_in_group" => array(
                "txt" => "stay_in_group"
            ),
            "recommended_level" => array(
                "txt" => "recommended_level"
            )
        );

        foreach ($this->all_cols as $k => $c) {
            $this->addColumn($this->pl->txt($c["txt"]));
        }

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate($this->pl->getDirectory() . "/templates/tpl.part_eval_ov_row.html");

        $this->pl->includeClass("class.cdParticipantEvaluation.php");
        $this->skill_vals = cdParticipantEvaluation::getSkillScaleValues($this->pl);


        $this->setExportFormats(array(self::EXPORT_EXCEL));
        $this->addMultiCommand("confirmPublishPartEval", $this->pl->txt("publish"));
        $this->addMultiCommand("unpublishPartEval", $this->pl->txt("unpublish"));
        //$this->addCommandButton("", $lng->txt(""));
    }
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        global $lng;

        $this->tpl->setVariable("PEID", $a_set["id"]);

        $skills = unserialize($a_set["skills"]);
        //var_dump($a_set);
        foreach ($this->all_cols as $k => $v) {
            switch ($k) {
                case "created_on":
                    $this->tpl->setVariable(
                        "VAL_CREATED",
                        ilDatePresentation::formatDate(new ilDateTime($a_set["creation_date"], IL_CAL_DATETIME))
                    );
                    break;

                case "last_update":
                    if ($a_set["creation_date"] != $a_set["update_date"]) {
                        $this->tpl->setVariable(
                            "VAL_UPDATED",
                            ilDatePresentation::formatDate(new ilDateTime($a_set["update_date"], IL_CAL_DATETIME))
                        );
                    }
                    break;

                case "course":
                    $this->tpl->setVariable("VAL_" . strtoupper($k), ilObject::_lookupTitle($a_set["course_id"]));
                    break;

                case "course_level":
                    $this->tpl->setVariable("VAL_" . strtoupper($k), ilObjCourse::_lookupLevel($a_set["course_id"]));
                    break;

                case "test_attendance":
                case "stay_in_group":
                case "published":
                    $this->tpl->setVariable("VAL_" . strtoupper($k), $a_set[$k] ? $lng->txt("yes") : $lng->txt("no"));
                    break;

                default:
                    if ($this->all_cols[$k]["skill"] == true) {
                        //var_dump($a_set["skills"]);
                        $this->tpl->setVariable("VAL_" . strtoupper($k), $this->skill_vals[$skills[$k]]);
                    } else {
                        $this->tpl->setVariable("VAL_" . strtoupper($k), $a_set[$k]);
                    }
                    break;
            }
        }
    }

    /**
     * Excel Version of Fill Row. Most likely to
     * be overwritten by derived class.
     *
     * @param	object	$a_worksheet	current sheet
     * @param	int		$a_row			row counter
     * @param	array	$a_set			data array
     */
    protected function fillRowExcel(ilExcel $a_excel, &$a_row, $a_set)
    {
        global $lng;

        $col = 0;
        $skills = unserialize($a_set["skills"]);

        foreach ($this->all_cols as $k => $c) {
            $val = "";
            switch ($k) {
                case "created_on":
                    $val = $a_set["creation_date"];
                    break;

                case "last_update":
                    if ($a_set["creation_date"] != $a_set["update_date"]) {
                        $val = $a_set["update_date"];
                    }
                    break;

                case "course":
                    $val = ilObject::_lookupTitle($a_set["course_id"]);
                    break;

                case "course_level":
                    $val = ilObjCourse::_lookupLevel($a_set["course_id"]);
                    break;

                case "test_attendance":
                case "stay_in_group":
                    $val = $a_set[$k] ? $lng->txt("yes") : $lng->txt("no");
                    break;

                default:
                    if ($this->all_cols[$k]["skill"] == true) {
                        $val = $this->skill_vals[$skills[$k]];
                    } else {
                        $val = $a_set[$k];
                    }
                    break;
            }

            $a_excel->setCell($a_row, $col, $val);
            $col++;
        }
    }
}
