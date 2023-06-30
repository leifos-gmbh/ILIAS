<?php

/**
 * Example configuration user interface class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 */
class ilCDConfigGUI extends ilPluginConfigGUI
{
    /**
    * Handles all commmands, default is "configure"
    */
    public function performCommand($cmd)
    {
        switch ($cmd) {
            default:
                $this->$cmd();
                break;

        }
    }

    /**
     * Configure
     *
     * @param
     * @return
     */
    public function configure()
    {
        global $tpl, $ilToolbar, $ilCtrl;

        $this->initConfigForm();
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Init config form.
     */
    public function initConfigForm()
    {
        global $lng, $ilCtrl, $ilSetting;
    
        $this->form = new ilPropertyFormGUI();
        
        // material ref id
        $ni = new ilNumberInputGUI($this->getPluginObject()->txt("material_ref_id"), "material_ref_id");
        $ni->setMaxLength(8);
        $ni->setSize(8);
        $ni->setValue($ilSetting->get("cd_mat_ref_id"));
        $this->form->addItem($ni);

        // learning studio href
        $ti = new ilTextInputGUI($this->getPluginObject()->txt("learning_studio"), "learning_studio");
        $ti->setValue($ilSetting->get("cd_learn_studio"));
        $this->form->addItem($ti);

    
        $this->form->addCommandButton("save", $lng->txt("save"));
                    
        $this->form->setTitle($this->getPluginObject()->txt("cd_config"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));
    }

    /**
     * Save  form
     */
    public function save()
    {
        global $tpl, $lng, $ilCtrl, $ilSetting;
    
        $this->initConfigForm();
        if ($this->form->checkInput()) {
            $ilSetting->set("cd_mat_ref_id", (int) $_POST["material_ref_id"]);
            $ilSetting->set("cd_learn_studio", $_POST["learning_studio"]);
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "configure");
        } else {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHtml());
        }
    }
}
