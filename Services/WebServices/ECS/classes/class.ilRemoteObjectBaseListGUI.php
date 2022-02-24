<?php

use ILIAS\UI\Component\Card\Card as Card;

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

    public function getAsCard(
        int $ref_id,
        int $obj_id,
        string $type,
        string $title,
        string $description
    ) : ?Card {

        $consent_gui = new ilECSUserConsentModalGUI(
            $this->user->getId(),
            $ref_id);
        if ($consent_gui->hasConsented()) {
            return parent::getAsCard($ref_id, $obj_id, $type, $title, $description);
        }

        $this->ctrl->setReturnByClass(
            $this->getGUIClassname(),
            'call'
        );
        $card = parent::getAsCard($ref_id, $obj_id, $type, $title, $description);
        if ($card instanceof Card) {
            return $consent_gui->addConsentModalToCard($card);
        }
    }

    public function createDefaultCommand($command)
    {
        $consent_gui = new ilECSUserConsentModalGUI(
            $this->user->getId(),
            $this->ref_id);
        $command = parent::createDefaultCommand($command);
        if ($consent_gui->hasConsented()) {
            return $command;
        }
        $command['link'] = '';
        $command['frame'] = '';
        return $command;
    }

    protected function getGUIClassname() : string
    {
        $classname = $this->obj_definition->getClassName($this->type);
        return 'ilObj' . $classname . 'GUI';
    }
}