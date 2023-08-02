<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * TableGUI class for courses list
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup Services
 */
class cdEvaluationNotificationsTableGUI extends ilTable2GUI
{
    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_pl, $a_items = "")
    {
        global $ilCtrl, $lng, $ilAccess, $lng, $objDefinition;
        
        $this->pl = $a_pl;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setTitle($this->pl->txt("evaluation_notifications"));
        
        $this->addColumn($this->lng->txt("obj_crs"));
        $this->addColumn($this->pl->txt("participant"));
        //		$this->addColumn($this->pl->txt("evaluations"));
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate($this->pl->getDirectory() . "/templates/tpl.eval_notification_row.html");
    }

    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        global $lng;
        
        if (is_array($a_set["users"])) {
            foreach ($a_set["users"] as $user) {
                $this->tpl->setCurrentBlock("user");
                $this->tpl->setVariable(
                    "USER",
                    ilUserUtil::getNamePresentation($user["usr_id"], false, false, "", true) .
                    " (" . (int) $user["cnt_eval"] . ")"
                );
                $this->tpl->parseCurrentBlock();
            }
        }
            
        $this->tpl->setVariable("COURSE", ilObject::_lookupTitle($a_set["crs_id"]));
        $this->tpl->setVariable("COURSE_LINK", ilLink::_getLink($a_set["crs_ref_id"]));
        if ($a_set["crs_activation"]["target_evals"] == 1) {
            $this->tpl->setVariable("CRS_TIME", $this->pl->txt("mid_of_course"));
        } elseif ($a_set["crs_activation"]["target_evals"] == 2) {
            $this->tpl->setVariable("CRS_TIME", $this->pl->txt("end_of_course"));
        }
    }
}
