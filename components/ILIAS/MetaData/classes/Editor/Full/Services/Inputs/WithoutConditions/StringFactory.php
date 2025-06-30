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

namespace ILIAS\MetaData\Editor\Full\Services\Inputs\WithoutConditions;

use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\MetaData\Vocabularies\Slots\ElementHelperInterface as ElementVocabSlotsHelper;
use ILIAS\MetaData\Vocabularies\Input\BridgeInterface as VocabInputBridge;
use ILIAS\UI\Component\Input\Field\Factory as UIFactory;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface;
use ILIAS\MetaData\Repository\Validation\Dictionary\DictionaryInterface as ConstraintDictionary;
use ILIAS\MetaData\Vocabularies\Slots\Identifier;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\Refinery\Factory as Refinery;

class StringFactory extends BaseFactory
{
    protected ElementVocabSlotsHelper $element_vocab_slots_helper;
    protected VocabInputBridge $vocab_input_bridge;
    protected Refinery $refinery;

    public function __construct(
        UIFactory $ui_factory,
        PresenterInterface $presenter,
        ConstraintDictionary $constraint_dictionary,
        ElementVocabSlotsHelper $element_vocab_slots_helper,
        VocabInputBridge $vocab_input_bridge,
        Refinery $refinery
    ) {
        parent::__construct($ui_factory, $presenter, $constraint_dictionary);
        $this->element_vocab_slots_helper = $element_vocab_slots_helper;
        $this->vocab_input_bridge = $vocab_input_bridge;
        $this->refinery = $refinery;
    }

    protected function rawInput(
        ElementInterface $element,
        ElementInterface $context_element,
        SlotIdentifier $conditional_slot = SlotIdentifier::NULL
    ): FormInput {
        $slot = $this->element_vocab_slots_helper->slotForElement($element);

        if (!$this->vocab_input_bridge->doesSlotHaveVocabularies($slot)) {
            return $this->buildTextInput(
                $this->getInputLabelFromElement($this->presenter, $element, $context_element),
                $element,
                true
            );
        }
        if (!$this->vocab_input_bridge->doesSlotAllowCustomInput($slot)) {
            return $this->buildSelectInput(
                $this->getInputLabelFromElement($this->presenter, $element, $context_element),
                $slot,
                $element,
                true
            );
        }
        return $this->buildRadioInput(
            $this->getInputLabelFromElement($this->presenter, $element, $context_element),
            $slot,
            $element
        );
    }

    protected function buildTextInput(
        string $label,
        ElementInterface $element,
        bool $with_value
    ): FormInput {
        $super_name = $element->getSuperElement()
                              ->getDefinition()
                              ->name();
        if ($super_name === 'description') {
            $input = $this->ui_factory->textarea($label);
        } else {
            $input = $this->ui_factory->text($label);
        }

        if ($with_value) {
            $input = $this->addValueFromElement($element, $input);
        }
        return $this->addConstraintsFromElement($this->constraint_dictionary, $element, $input, !$with_value);
    }

    /**
     * @return string[]
     */
    protected function buildSelectInput(
        string $label,
        SlotIdentifier $slot,
        ElementInterface $element,
        bool $with_value
    ): FormInput {
        $values = [];
        $data = $this->getValueFromElementOrConstraint($element);
        $raw_values = $this->vocab_input_bridge->valuesInVocabulariesForSlot($slot, $data);
        foreach ($this->presenter->data()->vocabularyValues($slot, ...$raw_values) as $labelled_value) {
            $values[$labelled_value->value()] = $labelled_value->label();
        }
        $input = $this->ui_factory->select($label, $values);

        if ($with_value) {
            $input = $this->addValueFromElement($element, $input);
        }
        return $this->addConstraintsFromElement($this->constraint_dictionary, $element, $input, !$with_value);
    }

    protected function buildRadioInput(
        string $label,
        SlotIdentifier $slot,
        ElementInterface $element
    ): FormInput {
        $data = $this->getValueFromElementOrConstraint($element);

        $value_label = $this->presenter->utilities()->txt('md_editor_value');
        $select_input = $this->buildSelectInput($value_label, $slot, $element, false);
        $text_input = $this->buildTextInput($value_label, $element, false);

        if (isset($data)) {
            if ($this->vocab_input_bridge->isValueInVocabulariesForSlot($slot, $data)) {
                $select_input = $select_input->withValue($data);
                $radio_value = 'from_vocab';
            } else {
                $text_input = $text_input->withValue($data);
                $radio_value = 'custom';
            }
        }

        $input = $this->ui_factory->switchableGroup(
            [
                'from_vocab' => $this->ui_factory->group(
                    ['value' => $select_input],
                    $this->presenter->utilities()->txt('md_editor_from_vocab_input')
                ),
                'custom' => $this->ui_factory->group(
                    ['value' => $text_input],
                    $this->presenter->utilities()->txt('md_editor_custom_input')
                )
            ],
            $label
        );
        if (isset($radio_value)) {
            $input = $input->withValue($radio_value);
        }
        return $this->addConstraintsFromElement($this->constraint_dictionary, $element, $input, true)
                    ->withAdditionalTransformation(
                        $this->refinery->custom()->transformation(function ($vs) {
                            return $vs[1]['value'] ?? null;
                        })
                    );
    }

    /**
     * Not enough to use addConstraintsFromElement here, since
     * the value might need to be added to the list of options.
     */
    protected function getValueFromElementOrConstraint(ElementInterface $element): ?string
    {
        $data = null;
        if ($element->getData()->type() !== Type::NULL) {
            $data = $element->getData()->value();
        }
        return $this->getPresetValueFromConstraints($this->constraint_dictionary, $element) ?? $data;
    }

    public function getInput(
        ElementInterface $element,
        ElementInterface $context_element
    ): FormInput {
        return $this->rawInput($element, $context_element);
    }

    public function getInputInCondition(
        ElementInterface $element,
        ElementInterface $context_element,
        SlotIdentifier $conditional_slot
    ): FormInput {
        return $this->rawInput($element, $context_element);
    }
}
