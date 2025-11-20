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

namespace ILIAS\MetaData\OERHarvester\ControlCenter\State;

use ILIAS\MetaData\OERHarvester\ExposedRecords\DatabaseRepository as ExposedRecordsRepository;
use ILIAS\MetaData\OERHarvester\ResourceStatus\DatabaseRepository as ResourceStatusRepository;
use ILIAS\MetaData\OERHarvester\Settings\SettingsInterface as PublishingSettings;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\OERHarvester\RepositoryObjects\HandlerInterface as RepoObjectHandler;
use ILIAS\MetaData\Copyright\Identifiers\HandlerInterface as CopyrightIdentifierHandler;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;

class StateInfoFetcher implements StateInfoFetcherInterface
{
    public function __construct(
        protected ExposedRecordsRepository $exposed_repo,
        protected ResourceStatusRepository $status_repo,
        protected PublishingSettings $publishing_settings,
        protected RepoObjectHandler $repo_object_handler,
        protected CopyrightIdentifierHandler $copyright_identifier_handler,
        protected NavigatorFactoryInterface $navigator_factory,
        protected PathFactory $path_factory
    ) {
    }

    public function getStateInfoForObjectReference(
        int $ref_id,
        int $obj_id,
        string $type,
        SetInterface $set
    ): StateInfoInterface {
        $is_publishing_relevant = $this->isPublishingRelevantForType($type);
        if (!$is_publishing_relevant) {
            return new StateInfo(false, Status::UNPUBLISHED, [], [], [], []);
        }
        $current_status = $this->getStatusForObject($obj_id);
        $eligible_copyright_entry_ids = $this->getEligibleCopyrightEntryIDs();
        return new StateInfo(
            true,
            $current_status,
            $this->getAllPossibleStatuses(),
            $this->getRelevantActions($current_status, $ref_id),
            $this->getUnavailableActions($current_status, $set, $eligible_copyright_entry_ids),
            $eligible_copyright_entry_ids
        );
    }

    protected function isPublishingRelevantForType(string $type): bool
    {
        return (
            $this->publishing_settings->isManualPublishingEnabled() ||
            $this->publishing_settings->isAutomaticPublishingEnabled()
            ) && in_array($type, $this->publishing_settings->getObjectTypesEligibleForPublishing());
    }

    public function getStatusForObject(int $obj_id): Status
    {
        if ($this->exposed_repo->doesRecordExistForObjID($obj_id)) {
            return Status::PUBLISHED;
        }
        if ($this->status_repo->isAlreadyHarvested($obj_id)) {
            return Status::UNDER_REVIEW;
        }
        if ($this->status_repo->isHarvestingBlocked($obj_id)) {
            return Status::BLOCKED;
        }
        return Status::UNPUBLISHED;
    }

    /**
     * @return Status[]
     */
    protected function getAllPossibleStatuses(): array
    {
        $statuses = [Status::UNPUBLISHED];
        if ($this->publishing_settings->isAutomaticPublishingEnabled()) {
            $statuses[] = Status::BLOCKED;
        }
        if ($this->publishing_settings->isEditorialStepEnabled()) {
            $statuses[] = Status::UNDER_REVIEW;
        }
        $statuses[] = Status::PUBLISHED;
        return $statuses;
    }


    /**
     * @return Action[]
     */
    protected function getRelevantActions(Status $status, int $ref_id): array
    {
        $actions = [];
        switch ($status) {
            case Status::UNPUBLISHED:
                if ($this->publishing_settings->isAutomaticPublishingEnabled()) {
                    $actions[] = Action::BLOCK;
                }
                if (!$this->publishing_settings->isManualPublishingEnabled()) {
                    break;
                }
                if ($this->publishing_settings->isEditorialStepEnabled()) {
                    $actions[] = Action::SUBMIT;
                } else {
                    $actions[] = Action::PUBLISH;
                }
                break;

            case Status::BLOCKED:
                $actions[] = Action::UNBLOCK;
                break;

            case Status::UNDER_REVIEW:
                if ($this->isReferenceInEditorialCategory($ref_id)) {
                    $actions[] = Action::ACCEPT;
                    $actions[] = Action::REJECT;
                } else {
                    $actions[] = Action::WITHDRAW;
                }
                break;

            case Status::PUBLISHED:
                $actions[] = Action::WITHDRAW;
        }
        return $actions;
    }

    protected function isReferenceInEditorialCategory(int $ref_id): bool
    {
        if (!$this->publishing_settings->isEditorialStepEnabled()) {
            return false;
        }
        return $this->repo_object_handler->isReferenceInContainer(
            $ref_id,
            $this->publishing_settings->getContainerRefIDForEditorialStep()
        );
    }

    /**
     * @return string[]
     */
    protected function getEligibleCopyrightEntryIDs(): array
    {
        return $this->publishing_settings->getCopyrightEntryIDsSelectedForPublishing();
    }

    /**
     * @param int[] $eligible_copyright_entry_ids
     * @return Action[]
     */
    protected function getUnavailableActions(
        Status $current_status,
        SetInterface $set,
        array $eligible_copyright_entry_ids
    ): array {
        if ($current_status !== Status::UNPUBLISHED) {
            return [];
        }

        $copyright_path = $this->path_factory
            ->custom()
            ->withNextStep('rights')
            ->withNextStep('description')
            ->withNextStep('string')
            ->get();
        $copyright_string  = $this->navigator_factory->navigator($copyright_path, $set->getRoot())
                                                     ->lastElementAtFinalStep()
                                                     ->getData()
                                                     ->value();
        if (!$this->copyright_identifier_handler->isIdentifierValid($copyright_string)) {
            return [Action::PUBLISH, Action::SUBMIT];
        }
        $entry_id = $this->copyright_identifier_handler->parseEntryIDFromIdentifier($copyright_string);
        if (!in_array($entry_id, $eligible_copyright_entry_ids)) {
            return [Action::PUBLISH, Action::SUBMIT];
        }
        return [];
    }
}
