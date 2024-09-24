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

namespace ILIAS\MetaData\Vocabularies\Controlled;

use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Vocabularies\Dispatch\Presentation\LabelledValueInterface;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;

interface RepositoryInterface
{
    /**
     * Returns ID of the created vocabulary
     * @throws \ilMDVocabulariesException if the values are invalid (in which case nothing is persisted)
     */
    public function create(
        SlotIdentifier $slot,
        string $source
    ): string;

    /**
     * The value, vocab_id tuple must be unique! Before using this,
     * check with findAlreadyExistingValues.
     */
    public function addValueToVocabulary(
        string $vocab_id,
        string $value,
        string $label = ''
    ): void;

    /**
     * @return string[]
     */
    public function findAlreadyExistingValues(
        SlotIdentifier $slot,
        string ...$values
    ): \Generator;

    public function getVocabulary(string $vocab_id): VocabularyInterface;

    /**
     * @return VocabularyInterface[]
     */
    public function getVocabulariesForSlots(SlotIdentifier ...$slots): \Generator;

    public function countActiveVocabulariesForSlot(SlotIdentifier $slot): int;

    /**
     * @return VocabularyInterface[]
     */
    public function getActiveVocabulariesForSlots(SlotIdentifier ...$slots): \Generator;

    public function isCustomInputAllowedForSlot(SlotIdentifier $slot): bool;

    /**
     * Values not from active controlled vocabularies will not be returned at all.
     * @return LabelledValueInterface[]
     */
    public function getLabelsForValues(
        SlotIdentifier $slot,
        string ...$values
    ): \Generator;

    public function setActiveForVocabulary(
        string $vocab_id,
        bool $active
    ): void;

    public function setCustomInputsAllowedForVocabulary(
        string $vocab_id,
        bool $custom_inputs
    ): void;

    public function deleteVocabulary(string $vocab_id): void;
}
