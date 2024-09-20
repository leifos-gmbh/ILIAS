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

namespace ILIAS\MetaData\Vocabularies\Standard;

use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Presentation\UtilitiesInterface as PresentationUtilities;

class NullRepository implements RepositoryInterface
{
    public function deactivateVocabulary(
        PathInterface $applicable_to,
        ?string $condition_value
    ): void {
    }

    public function activateVocabulary(
        PathInterface $applicable_to,
        ?string $condition_value
    ): void {
    }

    public function isVocabularyDeactivated(
        PathInterface $applicable_to,
        ?string $condition_value
    ): bool {
        return false;
    }

    public function countActiveVocabularies(PathInterface $applicable_to): int
    {
        return 0;
    }

    public function getVocabularies(PathInterface $applicable_to): \Generator
    {
        yield from [];
    }

    public function getActiveVocabularies(PathInterface $applicable_to): \Generator
    {
        yield from [];
    }

    public function getLabelsForValues(
        PresentationUtilities $presentation_utilities,
        PathInterface $path_to_element,
        string ...$values
    ): \Generator {
        yield from [];
    }
}
