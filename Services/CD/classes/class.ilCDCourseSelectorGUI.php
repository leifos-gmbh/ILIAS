<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Repository/classes/class.ilRepositorySelectorExplorerGUI.php");

/**
 * Course selctor
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 */
class ilCDCourseSelectorGUI extends ilRepositorySelectorExplorerGUI
{
    /**
     * Course selector
     *
     * @inheritdoc
     */
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        $a_selection_gui = null,
        $a_selection_cmd = "selectObject",
        $a_selection_par = "sel_ref_id"
    ) {
        parent::__construct($a_parent_obj, $a_parent_cmd, $a_selection_gui, $a_selection_cmd, $a_selection_par);
    }

    /**
     * Is node clickable?
     *
     * @param array $a_node node data
     * @return boolean node clickable true/false
     */
    public function isNodeClickable($a_node)
    {
        global $ilAccess;

        if ($this->select_postvar != "") {
            return false;
        }

        if (!$ilAccess->checkAccess("write", "", $a_node["child"]) || $a_node["type"] != "crs") {
            return false;
        }

        return true;
    }
}
