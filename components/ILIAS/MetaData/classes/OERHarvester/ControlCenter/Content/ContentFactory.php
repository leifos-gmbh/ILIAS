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

namespace ILIAS\MetaData\OERHarvester\ControlCenter\Content;

use ILIAS\MetaData\OERHarvester\ControlCenter\State\Status;
use ILIAS\UI\Component\Modal\RoundTrip as RoundTripModal;
use ILIAS\MetaData\OERHarvester\ControlCenter\Command;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\MetaData\OERHarvester\ControlCenter\State\StateInfoInterface;
use ILIAS\MetaData\Presentation\UtilitiesInterface as PresentationUtilities;
use ILIAS\UI\Component\Chart\ScaleBar;
use ILIAS\UI\Component\MessageBox\MessageBox;
use ILIAS\UI\Component\Button\Button;
use ILIAS\MetaData\OERHarvester\ControlCenter\Links\LinkFactoryInterface;
use ILIAS\MetaData\OERHarvester\ControlCenter\State\Action;
use ILIAS\MetaData\Copyright\RepositoryInterface;

class ContentFactory implements ContentFactoryInterface
{
    public function __construct(
        protected UIFactory $ui_factory,
        protected PresentationUtilities $presentation_utilities,
        protected LinkFactoryInterface $link_factory,
        protected RepositoryInterface $copyright_repository
    ) {
    }

    public function getInfoContent(StateInfoInterface $state_info): RoundTripModal
    {
        $message = $this->getStatusMessage($state_info);
        $scale = $this->getStatusOverview($state_info);
        $actions = $this->getActions($state_info);

        return $this->ui_factory->modal()->roundtrip(
            $this->presentation_utilities->txt('md_publishing_center_title'),
            [$message, $scale]
        )->withActionButtons($actions);
    }

    protected function getStatusOverview(StateInfoInterface $state_info): ScaleBar
    {
        $scale_items = [];
        foreach ($state_info->getAllPossibleStatuses() as $status) {
            $status_label = match ($status) {
                Status::UNPUBLISHED => $this->presentation_utilities->txt('md_publishing_status_unpublished'),
                Status::BLOCKED => $this->presentation_utilities->txt('md_publishing_status_blocked'),
                Status::UNDER_REVIEW => $this->presentation_utilities->txt('md_publishing_status_under_review'),
                Status::PUBLISHED => $this->presentation_utilities->txt('md_publishing_status_published')
            };
            $scale_items[$status_label] = ($status === $state_info->getCurrentStatus());
        }
        return $this->ui_factory->chart()->scaleBar($scale_items);
    }

    protected function getStatusMessage(StateInfoInterface $state_info): MessageBox
    {
        if (
            $state_info->getCurrentStatus() === Status::UNPUBLISHED &&
            !$state_info->isActionAvailable(Action::PUBLISH)
        ) {
            $valid_cp = [];
            foreach ($state_info->getAllEligibleCopyrightEntryIDs() as $copyright_id) {
                $valid_cp[] = $this->copyright_repository->getEntry($copyright_id)->title();
            }
            $valid_cp_list = '<br/><br/>' . implode('<br/>', $valid_cp);
            $message = $this->presentation_utilities->txtFill('md_publishing_info_wrong_copyright', $valid_cp_list);
        } else {
            $message = match ($state_info->getCurrentStatus()) {
                Status::UNPUBLISHED => $this->presentation_utilities->txt('md_publishing_info_unpublished'),
                Status::BLOCKED => $this->presentation_utilities->txt('md_publishing_info_blocked'),
                Status::UNDER_REVIEW => $this->presentation_utilities->txt('md_publishing_info_under_review'),
                Status::PUBLISHED => $this->presentation_utilities->txt('md_publishing_info_published')
            };
        }

        return $this->ui_factory->messageBox()->info($message);
    }

    /**
     * @return Button[]
     */
    protected function getActions(StateInfoInterface $state_info): array
    {
        $buttons = [];
        foreach ($state_info->getRelevantActions() as $action) {
            $link = $this->link_factory->getLinkForAction($action);
            $label = match ($action) {
                Action::BLOCK => $this->presentation_utilities->txt('md_publishing_action_block'),
                Action::UNBLOCK => $this->presentation_utilities->txt('md_publishing_action_unblock'),
                Action::PUBLISH => $this->presentation_utilities->txt('md_publishing_action_publish'),
                Action::WITHDRAW => $this->presentation_utilities->txt('md_publishing_action_withdraw'),
                Action::SUBMIT => $this->presentation_utilities->txt('md_publishing_action_submit'),
                Action::ACCEPT => $this->presentation_utilities->txt('md_publishing_action_accept'),
                Action::REJECT => $this->presentation_utilities->txt('md_publishing_action_reject')
            };
            $disabled = $state_info->isActionAvailable($action);
            $buttons[] = $this->ui_factory->button()->standard($label, $link)
                                                    ->withUnavailableAction($disabled);
        }
        return $buttons;
    }

    public function getConfirmationContent(Action $action): RoundTripModal
    {
        $message = match ($action) {
            Action::WITHDRAW => $this->presentation_utilities->txt('md_publishing_confirmation_info_withdraw'),
            Action::ACCEPT => $this->presentation_utilities->txt('md_publishing_confirmation_info_accept'),
            Action::REJECT => $this->presentation_utilities->txt('md_publishing_confirmation_info_reject'),
            default => ''
        };
        $message_box = $this->ui_factory->messageBox()->confirmation($message);
        $title = match ($action) {
            Action::WITHDRAW => $this->presentation_utilities->txt('md_publishing_confirmation_withdraw'),
            Action::ACCEPT => $this->presentation_utilities->txt('md_publishing_confirmation_accept'),
            Action::REJECT => $this->presentation_utilities->txt('md_publishing_confirmation_reject'),
            default => ''
        };
        $action = $this->link_factory->getLinkForConfirmationOfAction($action);
        $button = $this->ui_factory->button()->standard(
            $this->presentation_utilities->txt('confirm'),
            $action
        );
        return $this->ui_factory->modal()->roundtrip($title, $message_box)->withActionButtons([$button]);
    }
}
