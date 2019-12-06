<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjBlogListGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilObjBlogListGUI extends ilObjectListGUI
{
    /**
    * initialisation
    */
    public function init()
    {
        $this->copy_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true; // #10498
        $this->info_screen_enabled = true;
        $this->type = "blog";
        $this->gui_class_name = "ilobjbloggui";

        // general commands array
        $this->commands = ilObjBlogAccess::_getCommands();
    }
    
    public function getCommands()
    {
        $commands = parent::getCommands();
        
        // #10182 - handle edit and contribute
        $permissions = array();
        foreach ($commands as $idx => $item) {
            if ($item["lang_var"] == "edit" && $item["granted"]) {
                $permissions[$item["permission"]] = $idx;
            }
        }
        if (sizeof($permissions) == 2) {
            unset($commands[$permissions["contribute"]]);
        }
        
        return $commands;
    }

    /**
     * insert command button
     *
     * @access	private
     * @param	string		$a_href		link url target
     * @param	string		$a_text		link text
     * @param	string		$a_frame	link frame target
     */
    function insertCommand($a_href, $a_text, $a_frame = "", $a_img = "", $a_cmd = "", $a_onclick = "")
    {
        if ($a_cmd != "export") {
            parent::insertCommand($a_href, $a_text, $a_frame, $a_img, $a_cmd, $a_onclick);
        }
        // #11099
        $chksum = md5($a_href.$a_text);
        if($a_href == "#" ||
            !in_array($chksum, $this->prevent_duplicate_commands))
        {
            if($a_href != "#")
            {
                $this->prevent_duplicate_commands[] = $chksum;
            }

            $prevent_background_click = false;

            $signal = $this->initModal();
            $this->current_selection_list->addItem($a_text, "", $a_href, $a_img, $a_text, $a_frame,
                "", $prevent_background_click, "( function() { $(document).trigger('".$signal."', {'id': '".$signal."','triggerer':$(this), 'options': JSON.parse('[]')}); return false;})()");
        }
    }

    function insertCommands($a_use_asynch = false, $a_get_asynch_commands = false,
        $a_asynch_url = "", $a_header_actions = false) {
        $ret = parent::insertCommands($a_use_asynch, $a_get_asynch_commands,
            $a_asynch_url, $a_header_actions);

        if (!is_null($this->modal)) {
            global $DIC;
            $renderer = $DIC->ui()->renderer();
            $ret.= $renderer->render($this->modal);
        }
        return $ret;
    }

    /**
     *
     *
     * @param
     * @return
     */
    protected function initModal()
    {
        global $DIC;
        $factory = $DIC->ui()->factory();
        $ctrl = $DIC->ctrl();
        $renderer = $DIC->ui()->renderer();
        $this->modal = $factory->modal()->interruptive('HTML Export', "Should comments be included", $ctrl->getLinkTargetByClass("ilobjbloggui", "handleExportChoice"))
            ->withActionButtonLabel("action button label")
            ->withCancelButtonLabel("cancel button label");


        return $this->modal->getShowSignal();
    }


}
