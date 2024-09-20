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

use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Vocabularies\Dispatch\Presentation\LabelledValueInterface;
use ILIAS\MetaData\Vocabularies\Dispatch\Presentation\NullLabelledValue;

class NullRepository implements RepositoryInterface
{
    /**
     * Returns ID of the created vocabulary
     * @throws \ilMDVocabulariesException if the values are invalid (in which case nothing is persisted)
     */
    public function create(
        PathInterface $path_to_element,
        ?PathInterface $path_to_condition,
        ?string $condition_value,
        string $source
    ): string {
        return '';
    }

    public function addValueToVocabulary(
        string $vocab_id,
        string $value,
        ?string $label
    ): void {
    }

    /**
     * @return string[]
     */
    public function findAlreadyExistingValues(
        PathInterface $path_to_element,
        string ...$values
    ): \Generator {
        yield from [];
    }

    /**
     * @return VocabularyInterface[]
     */
    public function getVocabulariesForElement(
        PathInterface $path_to_element
    ): \Generator {
        yield from [];
    }

    public function countActiveVocabulariesForElement(PathInterface $path_to_element): int
    {
        return 0;
    }

    /**
     * @return VocabularyInterface[]
     */
    public function getActiveVocabulariesForElement(PathInterface $path_to_element): \Generator
    {
        yield from [];
    }

    public function isCustomInputAllowedForElement(PathInterface $path_to_element): bool
    {
        return false;
    }

    /**
     * @return LabelledValueInterface[]
     */
    public function getLabelsForValues(
        PathInterface $path_to_element,
        string ...$values
    ): \Generator {
        yield from [];
    }

    public function setActiveForVocabulary(
        string $vocab_id,
        bool $active
    ): void {
    }

    public function setCustomInputsAllowedForVocabulary(
        string $vocab_id,
        bool $custom_inputs
    ): void {
    }

    public function deleteVocabulary(string $vocab_id): void
    {
    }
}
