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

use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\MetaData\Vocabularies\Manager\Manager as VocabManager;
use ILIAS\MetaData\Presentation\ElementsInterface as ElementsPresentation;
use ILIAS\MetaData\Presentation\UtilitiesInterface as PresentationUtilities;
use ILIAS\MetaData\Vocabularies\Dispatch\Presentation\PresentationInterface as VocabPresentation;
use ILIAS\MetaData\Vocabularies\VocabularyInterface;
use ILIAS\MetaData\Vocabularies\Type as VocabType;
use ILIAS\MetaData\Vocabularies\Slots\HandlerInterface as SlotHandler;
use ILIAS\MetaData\Vocabularies\Slots\Identifier as SlotIdentifier;
use ILIAS\MetaData\Elements\Structure\StructureSetInterface as Structure;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface as NavigatorFactory;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Elements\Structure\StructureElementInterface;
use ILIAS\MetaData\Elements\Data\Type as DataType;
use ILIAS\MetaData\Vocabularies\Slots\Conditions\ConditionInterface;

class ilMDVocabulariesDataRetrieval implements DataRetrieval
{
    protected const MAX_PREVIEW_VALUES = 5;

    protected VocabManager $vocab_manager;
    protected ElementsPresentation $elements_presentation;
    protected PresentationUtilities $presentation_utils;
    protected VocabPresentation $vocab_presentation;
    protected SlotHandler $slot_handler;
    protected Structure $structure;
    protected NavigatorFactory $navigator_factory;

    /**
     * @var VocabularyInterface[]
     */
    protected array $vocabs;

    public function __construct(
        VocabManager $vocab_manager,
        ElementsPresentation $elements_presentation,
        PresentationUtilities $presentation_utils,
        VocabPresentation $vocab_presentation,
        SlotHandler $slot_handler,
        Structure $structure
    ) {
        $this->vocab_manager = $vocab_manager;
        $this->elements_presentation = $elements_presentation;
        $this->presentation_utils = $presentation_utils;
        $this->vocab_presentation = $vocab_presentation;
        $this->slot_handler = $slot_handler;
        $this->structure = $structure;
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): Generator {
        $infos = $this->vocab_manager->infos();
        foreach ($this->getVocabs($range) as $vocab) {
            $record = [];

            $record['element'] = $this->translateSlot($vocab->slot());
            $record['type'] = $this->translateType($vocab->type());
            $record['source'] = $vocab->source();
            $record['preview'] = $this->buildValuesPreview($vocab);
            $record['active'] = $vocab->isActive();
            if ($infos->isCustomInputApplicable($vocab)) {
                $record['custom_input'] = $vocab->allowsCustomInputs();
            }

            yield $row_builder->buildDataRow(
                $vocab->id(),
                $record
            )->withDisabledAction(
                'delete',
                !$infos->canBeDeleted($vocab)
            )->withDisabledAction(
                'toggle_active',
                $vocab->isActive() && !$infos->isDeactivatable($vocab)
            )->withDisabledAction(
                'toggle_custom_input',
                $infos->canDisallowCustomInput($vocab)
            );
        };
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return count($this->getVocabs());
    }

    protected function translateType(VocabType $type): string
    {
        return match ($type) {
            VocabType::STANDARD => $this->presentation_utils->txt('md_vocab_type_standard'),
            VocabType::CONTROLLED_STRING => $this->presentation_utils->txt('md_vocab_type_controlled_string'),
            VocabType::CONTROLLED_VOCAB_VALUE => $this->presentation_utils->txt('md_vocab_type_controlled_vocab_value'),
            VocabType::COPYRIGHT => $this->presentation_utils->txt('md_vocab_type_copyright'),
            default => '',
        };
    }

    protected function translateSlot(SlotIdentifier $slot): string
    {
        //skip the name of the element if it does not add any information
        $skip_data = [DataType::VOCAB_VALUE, DataType::STRING];

        $element = $this->getStructureElementFromPath(
            $this->slot_handler->pathForSlot($slot),
            $this->structure->getRoot()
        );
        $element_name = $this->elements_presentation->nameWithParents(
            $element,
            null,
            false,
            in_array($element->getDefinition()->dataType(), $skip_data),
        );

        if (!$this->slot_handler->isSlotConditional($slot)) {
            return $element_name;
        }

        $condition = $this->slot_handler->conditionForSlot($slot);

        $condition_element = $this->getStructureElementFromPath(
            $condition->path(),
            $element
        );
        $condition_element_name = $this->elements_presentation->nameWithParents(
            $element,
            $this->findFirstCommonParent($element, $condition_element)->getSuperElement(),
            false,
            in_array($element->getDefinition()->dataType(), $skip_data),
        );

        return $this->presentation_utils->txtFill(
            'md_vocab_element_with_condition',
            $element_name,
            $condition_element_name,
            $this->translateConditionValue($condition)
        );
    }

    protected function translateConditionValue(ConditionInterface $condition): string
    {
        $slot = $this->slot_handler->identiferFromPathAndCondition($condition->path(), null, null);
        return (string) $this->vocab_presentation->presentableLabels(
            $this->presentation_utils,
            $slot,
            false,
            $condition->value()
        )->current()?->label();
    }

    protected function findFirstCommonParent(
        StructureElementInterface $a,
        StructureElementInterface $b
    ): StructureElementInterface {
        while ($a !== $b) {
            $a = $a->getSuperElement();
            $b = $b->getSuperElement();
        }
        return $a;
    }

    protected function getStructureElementFromPath(
        PathInterface $path,
        StructureElementInterface $start
    ): StructureElementInterface {
        return $this->navigator_factory->structureNavigator(
            $path,
            $start
        )->elementAtFinalStep();
    }

    protected function buildValuesPreview(VocabularyInterface $vocabulary): string
    {
        $presentable_values = [];
        $i = 0;

        $labelled_values = $this->vocab_presentation->labelsForVocabulary(
            $this->presentation_utils,
            $vocabulary
        );
        foreach ($labelled_values as $labelled_value) {
            if ($i >= self::MAX_PREVIEW_VALUES) {
                $presentable_values[] = '...';
                break;
            }

            $presentable_value = $labelled_value->value();
            if ($labelled_value->label() !== '') {
                $presentable_value = $labelled_value->label() . ' (' . $presentable_value . ')';
            }
            $presentable_values[] = $presentable_value;
        }

        return implode(', ', $presentable_values);
    }

    protected function getVocabs(Range $range = null): array
    {
        if (isset($this->vocabs)) {
            return $this->vocabs;
        }

        $vocabs = iterator_to_array($this->vocab_manager->getAllVocabularies());
        if ($range) {
            $vocabs = array_slice($vocabs, $range->getStart(), $range->getLength());
        }

        return $this->vocabs = $vocabs;
    }
}
