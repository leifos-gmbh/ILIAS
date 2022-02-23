<?php

class ilRemoteObjectBaseListGUI extends ilObjectListGUI
{

    public function insertTitle()
    {
        $this->ctrl->setReturnByClass(
            //ilObjRemoteCategoryGUI::class,
            $this->getGUIClassname(),
            'call'
        );
        $consent_gui = new ilECSUserConsentModalGUI(
            $this->user->getId(),
            $this->ref_id);

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