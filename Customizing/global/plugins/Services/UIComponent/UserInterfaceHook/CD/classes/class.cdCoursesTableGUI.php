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
class cdCoursesTableGUI extends ilTable2GUI
{
    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_pl, $a_items = "")
    {
        global $ilCtrl, $lng, $ilAccess, $lng, $objDefinition;
        
        $this->pl = $a_pl;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData($a_items);
        $this->setTitle($lng->txt("objs_crs"));
        
        $this->addColumn($this->lng->txt("title"));
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate($this->pl->getDirectory() . "/templates/tpl.crs_list_row.html");
        
        // init list gui
        $class = $objDefinition->getClassName("crs");
        $location = $objDefinition->getLocation("crs");
        $full_class = "ilObj" . $class . "ListGUI";
        $item_list_gui = new $full_class();
        $item_list_gui->enableIcon(true);
        $item_list_gui->enableDelete(false);
        $item_list_gui->enableCut(false);
        $item_list_gui->enableCopy(false);
        $item_list_gui->enableLink(false);
        $item_list_gui->enableInfoScreen(false);
        $item_list_gui->enableSubscribe(false);
        $this->item_list_gui = $item_list_gui;
    }
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        global $lng;

        $html = $this->item_list_gui->getListItemHTML(
            $a_set["ref_id"],
            $a_set["obj_id"],
            $a_set["title"],
            $a_set["description"]
        );

        $this->tpl->setVariable("ITEM", $html);
    }
}
