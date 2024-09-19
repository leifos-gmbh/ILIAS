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

use ILIAS\MetaData\Paths\PathInterface;

class Factory implements FactoryInterface
{
    public const STANDARD_SOURCE = 'LOMv1.0';
    protected const COPYRIGHT_SOURCE = 'ILIAS';

    public function standard(PathInterface $applicable_to, string ...$values): BuilderInterface
    {
        return new Builder($applicable_to, Type::STANDARD, '', self::STANDARD_SOURCE, ...$values);
    }

    public function controlledString(
        PathInterface $applicable_to,
        string $id,
        string $source,
        string ...$values
    ): BuilderInterface {
        return new Builder($applicable_to, Type::CONTROLLED_STRING, $id, $source, ...$values);
    }

    public function controlledVocabValue(
        PathInterface $applicable_to,
        string $id,
        string $source,
        string ...$values
    ): BuilderInterface {
        return new Builder($applicable_to, Type::CONTROLLED_VOCAB_VALUE, $id, $source, ...$values);
    }

    public function copyright(PathInterface $applicable_to, string ...$values): BuilderInterface
    {
        return new Builder($applicable_to, Type::COPYRIGHT, '', self::COPYRIGHT_SOURCE, ...$values);
    }
}
