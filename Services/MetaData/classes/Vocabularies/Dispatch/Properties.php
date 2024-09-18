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

class Properties implements PropertiesInterface
{
    public function canBeDeactivated(VocabularyInterface $vocabulary): bool
    {
        switch ($vocabulary->type()) {
            case Type::STANDARD:
            case Type::CONTROLLED_VOCAB_VALUE:
                //only if there is at least one other active vocab

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

    public function toggleActive(VocabularyInterface $vocabulary): void
    {
    }

    public function toggleCustomInputAllowed(VocabularyInterface $vocabulary): void
    {
    }
}
