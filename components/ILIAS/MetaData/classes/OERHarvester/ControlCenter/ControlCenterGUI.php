<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\MetaData\OERHarvester\ControlCenter;

use ilPermissionException;
use ilCtrlInterface;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\MetaData\Presentation\UtilitiesInterface as PresentationUtilities;
use ILIAS\MetaData\OERHarvester\ControlCenter\State\StateInfoFetcherInterface;
use ILIAS\MetaData\OERHarvester\ControlCenter\State\StateInfoInterface;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\OERHarvester\ControlCenter\State\Action;
use ILIAS\MetaData\OERHarvester\ControlCenter\Content\ContentFactoryInterface;
use ILIAS\Data\URI;
use ILIAS\MetaData\OERHarvester\Publisher\PublisherInterface;

class ControlCenterGUI
{
    protected ilCtrlInterface $ctrl;
    protected UIFactory $ui_factory;
    protected UIRenderer $ui_renderer;
    protected ContentFactoryInterface $content_factory;
    protected PresentationUtilities $presentation_utilities;
    protected StateInfoFetcherInterface $state_info_fetcher;
    protected PublisherInterface $state_changer;

    protected StateInfoInterface $state_info;

    public function __construct(
        protected URI $link_to_parent,
        protected int $ref_id,
        protected int $obj_id,
        protected string $type,
        SetInterface $set
    ) {
        $this->state_info = $this->state_info_fetcher->getStateInfoForObjectReference(
            $ref_id,
            $obj_id,
            $type,
            $set
        );
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);

        $cmd = Command::tryFrom($this->ctrl->getCmd());
        switch ($next_class) {
            default:
                if (!$cmd || !$this->isCommandAvailable($cmd)) {
                    throw new ilPermissionException($this->presentation_utilities->txt('permission_denied'));
                }
                $cmd_value = $cmd->value;
                $this->$cmd_value();
                break;
        }
    }

    /**
     * Includes access checks via StateInfo
     */
    protected function isCommandAvailable(Command $cmd): bool
    {
        if (!$this->state_info->isPublishingRelevant()) {
            return false;
        }

        return match ($cmd) {
            Command::VIEW => true,
            Command::BLOCK => $this->state_info->isActionAvailable(Action::BLOCK),
            Command::UNBLOCK => $this->state_info->isActionAvailable(Action::UNBLOCK),
            Command::PUBLISH => $this->state_info->isActionAvailable(Action::PUBLISH),
            Command::WITHDRAW, Command::CONFIRM_WITHDRAW => $this->state_info->isActionAvailable(Action::WITHDRAW),
            Command::SUBMIT => $this->state_info->isActionAvailable(Action::SUBMIT),
            Command::ACCEPT, Command::CONFIRM_ACCEPT => $this->state_info->isActionAvailable(Action::ACCEPT),
            Command::REJECT, Command::CONFIRM_REJECT => $this->state_info->isActionAvailable(Action::REJECT)
        };
    }

    protected function view(): void
    {
        $content = $this->content_factory->getInfoContent($this->state_info);
        echo $this->ui_renderer->renderAsync($this->ui_factory->prompt()->state()->show($content));
        exit;
    }

    protected function block(): void
    {
        $this->state_changer->block($this->obj_id);
        echo $this->ui_renderer->renderAsync($this->ui_factory->prompt()->state()->redirect($this->link_to_parent));
        exit;
    }

    protected function unblock(): void
    {
        $this->state_changer->unblock($this->obj_id);
        echo $this->ui_renderer->renderAsync($this->ui_factory->prompt()->state()->redirect($this->link_to_parent));
        exit;
    }

    protected function publish(): void
    {
        $this->state_changer->publish($this->obj_id, $this->type);
        echo $this->ui_renderer->renderAsync($this->ui_factory->prompt()->state()->redirect($this->link_to_parent));
        exit;
    }

    protected function withdraw(): void
    {
        $content = $this->content_factory->getConfirmationContent(Action::WITHDRAW);
        echo $this->ui_renderer->renderAsync($this->ui_factory->prompt()->state()->show($content));
        exit;
    }

    protected function confirmWithdraw(): void
    {
        $this->state_changer->withdraw($this->obj_id);
        echo $this->ui_renderer->renderAsync($this->ui_factory->prompt()->state()->redirect($this->link_to_parent));
        exit;
    }

    protected function submit(): void
    {
        $this->state_changer->submit($this->obj_id);
        echo $this->ui_renderer->renderAsync($this->ui_factory->prompt()->state()->redirect($this->link_to_parent));
        exit;
    }

    protected function accept(): void
    {
        $content = $this->content_factory->getConfirmationContent(Action::ACCEPT);
        echo $this->ui_renderer->renderAsync($this->ui_factory->prompt()->state()->show($content));
        exit;
    }

    protected function confirmAccept(): void
    {
        $this->state_changer->accept($this->obj_id, $this->type);
        echo $this->ui_renderer->renderAsync($this->ui_factory->prompt()->state()->redirect($this->link_to_parent));
        exit;
    }

    protected function reject(): void
    {
        $content = $this->content_factory->getConfirmationContent(Action::REJECT);
        echo $this->ui_renderer->renderAsync($this->ui_factory->prompt()->state()->show($content));
        exit;
    }

    protected function confirmReject(): void
    {
        $this->state_changer->reject($this->obj_id);
        echo $this->ui_renderer->renderAsync($this->ui_factory->prompt()->state()->redirect($this->link_to_parent));
        exit;
    }
}
