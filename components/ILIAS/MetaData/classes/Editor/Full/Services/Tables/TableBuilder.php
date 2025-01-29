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

namespace ILIAS\MetaData\Editor\Full\Services\Tables;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\MetaData\Editor\Presenter\PresenterInterface as PresenterInterface;
use ILIAS\MetaData\Editor\Full\Services\DataFinder;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Editor\Full\Services\Actions\FlexibleSignal;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\MetaData\DataHelper\DataHelperInterface;
use ILIAS\MetaData\Editor\Http\Request;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Data\URI;

class TableBuilder
{
    protected UIFactory $ui_factory;
    protected PresenterInterface $presenter;
    protected DataHelperInterface $data_helper;
    protected DataFinder $data_finder;

    protected ElementInterface $template_element;
    protected array $raw_rows;

    public function __construct(
        UIFactory $ui_factory,
        PresenterInterface $presenter,
        DataHelperInterface $data_helper,
        DataFinder $data_finder
    ) {
        $this->ui_factory = $ui_factory;
        $this->presenter = $presenter;
        $this->data_helper = $data_helper;
        $this->data_finder = $data_finder;
    }

    public function get(Request $request): DataTable
    {
        $table = $this->init();
        return $request->applyRequestToDataTable($table);
    }

    public function withAdditionalRow(
        ElementInterface $element,
        FlexibleSignal $update_signal,
        ?FlexibleSignal $delete_signal
    ): TableBuilder {
        if (!isset($this->template_element)) {
            $this->template_element = $element;
        }

        $res = [];

        foreach ($this->data_finder->getDataCarryingElements(
            $element,
            true
        ) as $data_el) {
            if (!is_null($value = $this->extractDataValue($data_el))) {
                $res[$data_el->getDefinition()->name()] = $value;
            }
        }

        $res['disable_delete'] = false;

        $clone = clone $this;
        $clone->raw_rows[] = $res;
        return $clone;
    }

    protected function init(): DataTable
    {
        if (!isset($this->template_element)) {
            throw new \ilMDEditorException('Table cannot be empty.');
        }

        $columns = [];
        foreach ($this->data_finder->getDataCarryingElements(
            $this->template_element,
            true
        ) as $data_el) {
            $columns[$data_el->getDefinition()->name()] = $this->initColumn($data_el);
        }

        $name = $this->presenter->elements()->nameWithParents(
            $this->template_element,
            null,
            true
        );

        /**
         * TODO: figure out how to implement actions
         */
        $update_action = $this->ui_factory->table()->action()->single(
            $this->presenter->utilities()->txt('edit'),
        );
        $delete_action = $this->ui_factory->table()->action()->single(
            $this->presenter->utilities()->txt('delete'),
        );

        $table = $this->ui_factory->table()->data(
            $name,
            $columns,
            new DataRetrieval($this->raw_rows)
        );

        return $table->withActions([$update_action, $delete_action]);
    }

    protected function extractDataValue(
        ElementInterface $data_element
    ): null|string|int|\DateTimeImmutable {
        switch ($data_element->getData()->type()) {
            case Type::NON_NEG_INT:
                return (int) $data_element->getData()->value();

            case Type::DATETIME:
                return $this->data_helper->datetimeToObject($data_element->getData()->value());

            case Type::STRING:
            case Type::LANG:
            case Type::VOCAB_SOURCE:
            case Type::VOCAB_VALUE:
            case Type::DURATION:
                return $this->presenter->data()->dataValue($data_element->getData());

            case Type::NULL:
            default:
                return null;
        }
    }

    protected function initColumn(ElementInterface $data_element): Column
    {
        $name = $this->presenter->elements()->nameWithParents(
            $data_element,
            $this->template_element,
            false
        );

        switch ($data_element->getDefinition()->dataType()) {
            case Type::NON_NEG_INT:
                $column = $this->ui_factory->table()->column()->number($name);
                break;

            case Type::DATETIME:
                $column = $this->ui_factory->table()->column()->date(
                    $name,
                    $this->presenter->utilities()->getUserDateFormat()
                );
                break;

            case Type::NULL:
            case Type::STRING:
            case Type::LANG:
            case Type::VOCAB_SOURCE:
            case Type::VOCAB_VALUE:
            case Type::DURATION:
            default:
                $column = $this->ui_factory->table()->column()->text($name);
                break;
        }

        return $column->withIsSortable(false)
                      ->withIsOptional(false);
    }
}
