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
use ILIAS\MetaData\Elements\Base\BaseElementInterface;
use ILIAS\MetaData\Vocabularies\Type;

class Dispatcher implements DispatcherInterface
{
    /**
     * @return VocabularyInterface[]
     */
    public function vocabulariesForElement(
        BaseElementInterface $element
    ): \Generator {
    }

    /**
     * @return VocabularyInterface[]
     */
    public function activeVocabulariesForElement(
        BaseElementInterface $element
    ): \Generator {
    }

    public function canBeDeleted(VocabularyInterface $vocabulary): bool
    {
        switch ($vocabulary->type()) {
            case Type::CONTROLLED_STRING:
                return true;

            case Type::CONTROLLED_VOCAB_VALUE:
                //only if there is at least one other active vocab

            default:
            case Type::STANDARD:
            case Type::COPYRIGHT:
                return false;
        }
    }

    public function delete(VocabularyInterface $vocabulary): void
    {
    }
}
