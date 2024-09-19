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

namespace ILIAS\MetaData\Vocabularies\Dispatch;

use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Vocabularies\Type;
use ILIAS\MetaData\Vocabularies\Controlled\RepositoryInterface as ControlledRepo;
use ILIAS\MetaData\Vocabularies\Standard\DeactivationRepositoryInterface;

class Properties implements PropertiesInterface
{
    protected ControlledRepo $controlled_repo;
    protected DeactivationRepositoryInterface $deactivation_repo;

    public function __construct(
        ControlledRepo $controlled_repo,
        DeactivationRepositoryInterface $deactivation_repo
    ) {
        $this->controlled_repo = $controlled_repo;
        $this->deactivation_repo = $deactivation_repo;
    }

    public function canBeDeactivated(VocabularyInterface $vocabulary): bool
    {
        switch ($vocabulary->type()) {
            case Type::STANDARD:
            case Type::CONTROLLED_VOCAB_VALUE:
                $path = $vocabulary->applicableTo();
                $other_active_repositories_count =
                    ((int) $this->deactivation_repo->isStandardVocabularyDeactivated($path)) +
                    $this->controlled_repo->countActiveVocabulariesForElement($path) -
                    ((int) $vocabulary->isActive());
                return $other_active_repositories_count > 0;

            case Type::CONTROLLED_STRING:
                return true;

            default:
            case Type::COPYRIGHT:
                return false;
        }
    }

    public function canDisallowCustomInput(VocabularyInterface $vocabulary): bool
    {
        switch ($vocabulary->type()) {
            case Type::CONTROLLED_STRING:
                return true;

            default:
            case Type::CONTROLLED_VOCAB_VALUE:
            case Type::STANDARD:
            case Type::COPYRIGHT:
                return false;
        }
    }

    public function isCustomInputApplicable(VocabularyInterface $vocabulary): bool
    {
        switch ($vocabulary->type()) {
            case Type::CONTROLLED_STRING:
            case Type::COPYRIGHT:
                return true;

            default:
            case Type::CONTROLLED_VOCAB_VALUE:
            case Type::STANDARD:
                return false;
        }
    }

    public function activate(VocabularyInterface $vocabulary): void
    {
        switch ($vocabulary->type()) {
            case Type::STANDARD:
                $this->deactivation_repo->activateStandardVocabulary(
                    $vocabulary->applicableTo()
                );
                break;

            case Type::CONTROLLED_STRING:
            case Type::CONTROLLED_VOCAB_VALUE:
                $this->controlled_repo->setActiveForVocabulary(
                    $vocabulary->id(),
                    true
                );
                break;

            default:
            case Type::COPYRIGHT:
                break;
        }
    }

    public function deactivate(VocabularyInterface $vocabulary): void
    {
        if (!$this->canBeDeactivated($vocabulary)) {
            throw new \ilMDVocabulariesException('Vocabulary cannot be deactivated.');
        }

        switch ($vocabulary->type()) {
            case Type::STANDARD:
                $this->deactivation_repo->deactivateStandardVocabulary(
                    $vocabulary->applicableTo()
                );
                break;

            case Type::CONTROLLED_STRING:
            case Type::CONTROLLED_VOCAB_VALUE:
                $this->controlled_repo->setActiveForVocabulary(
                    $vocabulary->id(),
                    false
                );
                break;

            default:
            case Type::COPYRIGHT:
                break;
        }
    }

    public function allowCustomInput(VocabularyInterface $vocabulary): void
    {
        if (!$this->isCustomInputApplicable($vocabulary)) {
            throw new \ilMDVocabulariesException('Custom input is not applicable for vocabulary.');
        }

        switch ($vocabulary->type()) {
            case Type::CONTROLLED_STRING:
                $this->controlled_repo->setCustomInputsAllowedForVocabulary(
                    $vocabulary->id(),
                    true
                );
                break;

            default:
            case Type::CONTROLLED_VOCAB_VALUE:
            case Type::STANDARD:
            case Type::COPYRIGHT:
                break;
        }
    }

    public function disallowCustomInput(VocabularyInterface $vocabulary): void
    {
        if (
            !$this->isCustomInputApplicable($vocabulary) ||
            $this->canDisallowCustomInput($vocabulary)
        ) {
            throw new \ilMDVocabulariesException('Custom input cannot be disallowed for vocabulary.');
        }

        switch ($vocabulary->type()) {
            case Type::CONTROLLED_STRING:
                $this->controlled_repo->setCustomInputsAllowedForVocabulary(
                    $vocabulary->id(),
                    false
                );
                break;

            default:
            case Type::CONTROLLED_VOCAB_VALUE:
            case Type::STANDARD:
            case Type::COPYRIGHT:
                break;
        }
    }
}
