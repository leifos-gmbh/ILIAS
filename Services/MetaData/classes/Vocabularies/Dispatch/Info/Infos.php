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

namespace ILIAS\MetaData\Vocabularies\Dispatch\Info;

use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Vocabularies\Type;
use ILIAS\MetaData\Vocabularies\Controlled\RepositoryInterface as ControlledRepo;
use ILIAS\MetaData\Vocabularies\Standard\RepositoryInterface as StandardRepo;

class Infos implements InfosInterface
{
    protected ControlledRepo $controlled_repo;
    protected StandardRepo $standard_repo;

    public function __construct(
        ControlledRepo $controlled_repo,
        StandardRepo $standard_repo
    ) {
        $this->controlled_repo = $controlled_repo;
        $this->standard_repo = $standard_repo;
    }

    public function isDeactivatable(VocabularyInterface $vocabulary): bool
    {
        switch ($vocabulary->type()) {
            case Type::STANDARD:
            case Type::CONTROLLED_VOCAB_VALUE:
                return $this->hasElementOfVocabularyAnotherActiveVocabulary($vocabulary);

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

    public function canBeDeleted(VocabularyInterface $vocabulary): bool
    {
        switch ($vocabulary->type()) {
            case Type::CONTROLLED_STRING:
                return true;

            case Type::CONTROLLED_VOCAB_VALUE:
                return $this->hasElementOfVocabularyAnotherActiveVocabulary($vocabulary);

            default:
            case Type::STANDARD:
            case Type::COPYRIGHT:
                return false;
        }
    }

    /**
     * Whether the given vocabulary is active or not is irrelevant.
     */
    protected function hasElementOfVocabularyAnotherActiveVocabulary(VocabularyInterface $vocabulary): bool
    {
        $path = $vocabulary->applicableTo();
        $other_active_repositories_count =
            $this->standard_repo->countActiveVocabularies($path) +
            $this->controlled_repo->countActiveVocabulariesForElement($path) -
            ((int) $vocabulary->isActive());
        return $other_active_repositories_count > 0;
    }
}
