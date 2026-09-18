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

namespace ILIAS\UI\Implementation\Component\Input\ViewControl;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Input\ViewControl as ViewControlInterface;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\Refinery\Transformation;

class Section extends ViewControlInput implements ViewControlInterface\Section, HasInputGroup
{
    use GroupDecorator;

    private const SELECT_INDEX = 0;

    /** @var array<string|int, string> */
    protected array $options;
    protected Signal $internal_selection_signal;

    public function __construct(
        FieldFactory $field_factory,
        DataFactory $data_factory,
        Refinery $refinery,
        SignalGeneratorInterface $signal_generator,
        array $options
    ) {
        parent::__construct($data_factory, $refinery);

        $keys = array_keys($options);
        $this->checkArg('options', count($options) > 0, 'At least one section must be provided.');
        $this->checkArgListElements('options', $keys, ['string', 'int']);
        $this->checkArgListElements('options', $options, 'string');
        $this->options = $options;

        $this->setInputGroup(
            $field_factory->group([
                $field_factory->select('', $options)->withValue(array_key_first($options)),
            ])->withAdditionalTransformation($this->getSectionTransformation())
        );
        $this->internal_selection_signal = $signal_generator->create();
    }

    protected function isClientSideValueOk($value): bool
    {
        return (is_string($value) || is_int($value)) && array_key_exists($value, $this->options);
    }

    public function withValue($value): self
    {
        $this->checkArg('value', $value === null || $this->isClientSideValueOk($value), 'Display value does not match input type.');
        $clone = clone $this;
        $clone->setInputGroup($clone->getInputGroup()->withValue([$value]));
        return $clone;
    }

    public function getValue()
    {
        return $this->getInputGroup()->getValue()[self::SELECT_INDEX] ?? null;
    }

    protected function getSectionTransformation(): Transformation
    {
        return $this->refinery->custom()->transformation(
            static fn(array $value) => $value[self::SELECT_INDEX] ?? null
        );
    }

    /**
     * @return array<string|int, string>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function getInternalSignal(): Signal
    {
        return $this->internal_selection_signal;
    }
}
