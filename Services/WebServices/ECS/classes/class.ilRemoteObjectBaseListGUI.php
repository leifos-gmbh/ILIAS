<?php

class ilRemoteObjectBaseListGUI extends ilObjectListGUI
{

    public function insertTitle()
    {

        $this->ctrl->setReturnByClass(
            $this->getGUIClassname(),
            'call'
        );
        $consent_gui = new ilECSUserConsentModalGUI(
            $this->user->getId(),
            $this->ref_id);

        $shy_modal = $consent_gui->getTitleLink();
        if (!strlen($shy_modal)) {
            parent::insertTitle();
            return;
        }

        $this->tpl->setCurrentBlock("item_title");
        $this->tpl->setVariable("TXT_TITLE", $consent_gui->getTitleLink());
        $this->tpl->parseCurrentBlock();
    }


    protected function getGUIClassname() : string
    {
        $classname = $this->obj_definition->getClassName($this->type);
        return 'ilObj' . $classname . 'GUI';
    }
}