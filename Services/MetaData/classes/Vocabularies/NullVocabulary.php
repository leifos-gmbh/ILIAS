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

namespace ILIAS\MetaData\Vocabularies;

use ILIAS\MetaData\Vocabularies\Conditions\ConditionInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\NullPath;

class NullVocabulary implements VocabularyInterface
{
    public function applicableTo(): PathInterface
    {
        return new NullPath();
    }

    public function type(): Type
    {
        return Type::NULL;
    }

    public function id(): string
    {
        return '';
    }


    public function source(): string
    {
        return '';
    }

    /**
     * @return string[]
     */
    public function values(): \Generator
    {
        yield from [];
    }

    public function isConditional(): bool
    {
        return false;
    }

    public function condition(): ?ConditionInterface
    {
        return null;
    }

    public function isActive(): bool
    {
        return false;
    }

    public function allowsCustomInputs(): bool
    {
        return false;
    }
}
