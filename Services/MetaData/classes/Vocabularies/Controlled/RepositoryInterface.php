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

interface RepositoryInterface
{
    /**
     * Returns ID of the created vocabulary
     */
    public function create(
        PathInterface $path_to_element,
        ?PathInterface $path_to_condition,
        ?string $condition_value,
        string $source,
        string ...$values
    ): int;

    public function setLabelForValue(
        PathInterface $path_to_element,
        string $source,
        string $value,
        string $label
    ): void;

    /**
     * @return VocabularyInterface[]
     */
    public function getVocabulariesForElement(
        PathInterface $path_to_element
    ): \Generator;

    /**
     * @return VocabularyInterface[]
     */
    public function getActiveVocabulariesForElement(PathInterface $path_to_element): \Generator;

    public function isCustomInputAllowedForElement(PathInterface $path_to_element): bool;

    /**
     * Should return a LabelledValue object. Are
     * values (at least those with a label) really unique across
     * all controlled vocabularies of an element?
     */
    public function getLabelsForValues(
        PathInterface $path_to_element,
        string ...$values
    ): \Generator;

    public function setActiveForVocabulary(
        int $vocab_id,
        bool $active
    ): void;

    public function setCustomInputsAllowedForVocabulary(
        int $vocab_id,
        bool $custom_inputs
    ): void;

    public function deleteVocabulary(int $vocab_id): void;
}
