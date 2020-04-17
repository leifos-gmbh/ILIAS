<?php

/**
 * Videocast plugin
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_isCalledBy ilPCVideoCastPluginGUI: ilPCPluggedGUI
 * @ilCtrl_Calls ilPCVideoCastPluginGUI: ilPropertyFormGUI
 */
class ilPCVideoCastPluginGUI extends ilPageComponentPluginGUI
{
    /**
     * Execute command
     */
    function executeCommand()
    {
        global $ilCtrl;

        $next_class = $ilCtrl->getNextClass();

        switch ($next_class) {

            case "ilpropertyformgui":
                $form = $this->initForm(true);
                $ilCtrl->forwardCommand($form);
                break;

            default:
                // perform valid commands
                $cmd = $ilCtrl->getCmd();
                if (in_array($cmd, array("create", "save", "edit", "edit2", "update", "cancel"))) {
                    $this->$cmd();
                }
                break;
        }
    }

    /**
     * Form for new elements
     */
    function insert()
    {
        global $tpl;

        $form = $this->initForm(true);
        $tpl->setContent($form->getHTML());
    }

    /**
     * Save new pc example element
     */
    public function create()
    {
        global $tpl, $lng, $ilCtrl;

        $form = $this->initForm(true);
        if ($form->checkInput()) {
            $properties = array(
                "mcst" => $form->getInput("mcst"),
                "mode" => $form->getInput("mode")
            );
            if ($this->createElement($properties)) {
                ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
                $this->returnToParent();
            }
        }

        $form->setValuesByPost();
        $tpl->setContent($form->getHtml());
    }

    /**
     * Edit
     * @param
     * @return
     */
    function edit()
    {
        global $tpl;

        $this->setTabs("edit");

        $form = $this->initForm();
        $tpl->setContent($form->getHTML());
    }

    /**
     * Update
     * @param
     * @return
     */
    function update()
    {
        global $tpl, $lng, $ilCtrl;

        $form = $this->initForm(true);
        if ($form->checkInput()) {
            $properties = array(
                "mcst" => $form->getInput("mcst"),
                "mode" => $form->getInput("mode")
            );
            if ($this->updateElement($properties)) {
                ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
                $this->returnToParent();
            }
        }

        $form->setValuesByPost();
        $tpl->setContent($form->getHtml());

    }

    /**
     * Init editing form
     * @param int $a_mode Edit Mode
     */
    public function initForm($a_create = false)
    {
        global $lng, $ilCtrl;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        // mediacast
        $mcst = new ilRepositorySelector2InputGUI($this->getPlugin()->txt("obj_mcst"), "mcst", false);
        $exp = $mcst->getExplorerGUI();
        //$exp->setSkipRootNode(true);
        //$exp->setRootId($this->parent_ref_id);
        $exp->setSelectableTypes(["mcst"]);
        $exp->setTypeWhiteList(["root", "mcst", "cat", "crs", "grp", "fold"]);
        $mcst->setRequired(true);
        $form->addItem($mcst);

        // mode
        $options = array(
            "" => $this->getPlugin()->txt("full_view"),
            "m" => $this->getPlugin()->txt("main"),
            "s" => $this->getPlugin()->txt("side")
        );
        $si = new ilSelectInputGUI($this->getPlugin()->txt("mode"), "mode");
        $si->setOptions($options);
        $form->addItem($si);

        if (!$a_create) {
            $prop = $this->getProperties();
            $mcst->setValue($prop["mcst"]);
            $si->setValue($prop["mode"]);
        }

        // save and cancel commands
        if ($a_create) {
            $this->addCreationButton($form);
            $form->addCommandButton("cancel", $lng->txt("cancel"));
            $form->setTitle($this->getPlugin()->txt("cmd_insert"));
        } else {
            $form->addCommandButton("update", $lng->txt("save"));
            $form->addCommandButton("cancel", $lng->txt("cancel"));
            $form->setTitle($this->getPlugin()->txt("edit_videocast"));
        }

        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }

    /**
     * Cancel
     */
    function cancel()
    {
        $this->returnToParent();
    }

    /**
     * Get HTML for element
     * @param string $a_mode (edit, presentation, preview, offline)
     * @return string $html
     */
    function getElementHTML($a_mode, array $prop, $a_plugin_version)
    {
        global $DIC;

        $lng = $DIC->language();
        $lng->loadLanguageModule("mcst");
        $ctrl = $DIC->ctrl();

        if ($a_mode == "edit") {
            switch ($prop["mode"]) {
                case "m":
                    return "<div style='text-align:center; height:300px; padding: 100px 0; background-color:#fcf0ba;'>Main Video</div>";
                    break;

                case "s":
                    return "<div style='text-align:center; height:300px; padding: 100px 0; background-color:#fcf0ba;'>Side Column</div>";
                    break;

                default:
                    return "<div style='text-align:center; height:300px; padding: 100px 0; background-color:#fcf0ba;'>Full View</div>";
                    break;
            }
        }

        $mtpl = $DIC->ui()->mainTemplate();
        $pl = $this->getPlugin();
        $ref_id = (int) $prop["mcst"];
        $obj_id = ilObject::_lookupObjId($prop["mcst"]);
        if (ilObject::_lookupType($obj_id) == "mcst") {
            include_once("./Modules/MediaCast/Presentation/class.VideoViewGUI.php");
            $view = new \ILIAS\MediaCast\Presentation\VideoViewGUI(new ilObjMediaCast($ref_id), $mtpl);

            $ctrl->setParameterByClass("ilObjMediaCastGUI", "ref_id", $ref_id);
            $view->setCompletedCallback($ctrl->getLinkTargetByClass(["ilMediaCastHandlerGUI", "ilObjMediaCastGUI"],
                "handlePlayerCompletedEvent", "", true, false));


            switch ($prop["mode"]) {
                case "m":
                    return $view->renderMainColumn();
                    break;

                case "s":
                    return $view->renderSideColumn();
                    break;

                default:
                    return $view->render();
                    break;
            }

        }
        return "";
    }

    /**
     * Set tabs
     * @param
     * @return
     */
    function setTabs($a_active)
    {
        global $ilTabs, $ilCtrl;

        $pl = $this->getPlugin();

        /*
        $ilTabs->addTab("edit", $pl->txt("settings_1"),
            $ilCtrl->getLinkTarget($this, "edit"));

        $ilTabs->addTab("edit2", $pl->txt("settings_2"),
            $ilCtrl->getLinkTarget($this, "edit2"));

        $ilTabs->activateTab($a_active);*/
    }


}