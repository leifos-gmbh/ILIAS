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

namespace ILIAS\MetaData\Vocabularies\Input;

use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\MetaData\Vocabularies\Dispatch\ReaderInterface;

class Bridge implements BridgeInterface
{
    /**
     * TODO unit test this!
     */

    protected ReaderInterface $reader;

    protected array $cached_vocabularies_by_slot = [];

    public function __construct(ReaderInterface $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @return VocabularyInterface[]
     */
    protected function vocabulariesForSlot(
        SlotIdentifier $slot
    ): \Generator {
        if (!isset($this->cached_vocabularies_by_slot[$slot->value])) {
            $this->cached_vocabularies_by_slot[$slot->value] = iterator_to_array(
                $this->reader->activeVocabulariesForSlots($slot),
                false
            );
        }
        yield from $this->cached_vocabularies_by_slot[$slot->value];
    }

    public function doesSlotHaveVocabularies(SlotIdentifier $slot): bool
    {
        return $this->vocabulariesForSlot($slot)->current() !== null;
    }

    public function doesSlotAllowCustomInput(SlotIdentifier $slot): bool
    {
        foreach ($this->vocabulariesForSlot($slot) as $vocab) {
            if (!$vocab->allowsCustomInputs()) {
                return false;
            }
        }
        return true;
    }

    public function isValueInVocabulariesForSlot(
        SlotIdentifier $slot,
        string $value
    ): bool {
        foreach ($this->vocabulariesForSlot($slot) as $vocab) {
            if (in_array($value, iterator_to_array($vocab->values()), true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return string[]
     */
    public function valuesInVocabulariesForSlot(
        SlotIdentifier $slot,
        ?string $add_if_not_included = null
    ): \Generator {
        $values = [];
        foreach ($this->vocabulariesForSlot($slot) as $vocab) {
            $values_from_vocab = iterator_to_array($vocab->values());
            $values = array_merge($values, $values_from_vocab);
        }

        if (isset($add_if_not_included) && !in_array($add_if_not_included, $values)) {
            array_unshift($values, $add_if_not_included);
        }
        yield from $values;
    }

    public function sourceMapForSlot(SlotIdentifier $slot): \Closure
    {
        $sources_by_value = [];
        foreach ($this->vocabulariesForSlot($slot) as $vocab) {
            $values_from_vocab = iterator_to_array($vocab->values());
            $sources_by_value = array_merge(
                $sources_by_value,
                array_fill_keys($values_from_vocab, $vocab->source())
            );
        }

        return function (string $value) use ($sources_by_value) {
            return $sources_by_value[$value] ?? null;
        };
    }
}
