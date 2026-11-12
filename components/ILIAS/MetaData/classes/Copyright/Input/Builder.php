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

namespace ILIAS\MetaData\Copyright\Input;

use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Copyright\RepositoryInterface;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\MetaData\Repository\IdentifierHandler\IdentifierHandlerInterface;
use ILIAS\MetaData\Copyright\Identifiers\HandlerInterface;
use ILIAS\MetaData\Presentation\UtilitiesInterface as Presentation;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\MetaData\Copyright\EntryInterface;

class Builder implements BuilderInterface
{
    protected bool $with_custom_input = false;
    protected bool $with_non_featured_entries = false;
    protected string $custom_value = '';
    protected ?EntryInterface $preset_value = null;

    public function __construct(
        protected RepositoryInterface $repo,
        protected HandlerInterface $identifier_handler,
        protected Presentation $presentation,
        protected UIFactory $ui_factory,
        protected Refinery $refinery
    ) {
    }

    public function withCustomValue(string $value): BuilderInterface
    {
        $clone = clone $this;
        $clone->custom_value = $value;
        $clone->preset_value = null;
        return $clone;
    }

    public function withPresetValue(EntryInterface $value): BuilderInterface
    {
        $clone = clone $this;
        $clone->preset_value = $value;
        $clone->custom_value = '';
        return $clone;
    }

    public function withCustomInput(): BuilderInterface
    {
        $clone = clone $this;
        $clone->with_custom_input = true;
        return $clone;
    }

    public function withNonFeaturedEntries(): BuilderInterface
    {
        $clone = clone $this;
        $clone->with_non_featured_entries = true;
        return $clone;
    }

    public function get(): FormInput
    {
        $ff = $this->ui_factory->input()->field();

        $radio_groups = [];

        if ($this->hasOutdatedPresetValue()) {
            $radio_groups[$this->getPresetValue()->id()] = $ff->group(
                [],
                '(' . $this->presentation->txt('meta_copyright_outdated') . ') ' . $this->getPresetValue()->title(),
                $this->getPresetValue()->description()
            );
        }

        $not_featured_select_options = [];
        foreach ($this->repo->getActiveEntries() as $entry) {
            if (!$entry->isFeatured()) {
                if ($this->with_non_featured_entries) {
                    $not_featured_select_options[$entry->id()] = $entry->title();
                }
                continue;
            }
            $radio_groups[$entry->id()] = $ff->group([], $entry->title(), $entry->description());
        }

        if ($not_featured_select_options !== []) {
            $select = $ff->select(
                $this->presentation->txt('meta_non_featured_licences_select_label'),
                $not_featured_select_options
            )->withRequired(true);
            if ($this->hasInUsePresetValue()) {
                $select = $select->withValue($this->getPresetValue()->id());
            }
            $radio_groups['other'] = $ff->group(
                ['licence' => $select],
                $this->presentation->txt('meta_non_featured_licences_group_label')
            );
        }

        if ($this->with_custom_input) {
            $custom_text = $ff->textarea($this->presentation->txt('meta_custom_cp_description'));
            if ($this->hasCustomValue()) {
                $custom_text = $custom_text->withValue($this->getCustomValue());
            }
            $radio_groups['custom_input'] = $ff->group(
                ['description' => $custom_text],
                $this->presentation->txt('meta_cp_own')
            );
        }

        $input = $ff->switchableGroup(
            $radio_groups,
            $this->presentation->txt('meta_copyright')
        )->withAdditionalTransformation(
            $this->refinery->custom()->transformation(function ($vs) {
                if ($vs[0] === 'other') {
                    return $this->identifier_handler->buildIdentifierFromEntryID((int) $vs[1]['licence']);
                }
                if ($vs[0] === 'custom_input') {
                    return (string) $vs[1]['description'];
                }
                return $this->identifier_handler->buildIdentifierFromEntryID((int) $vs[0]);
            })
        );

        if ($this->with_custom_input && $this->hasCustomValue()) {
            $input = $input->withValue('custom_input');
        } elseif ($this->with_non_featured_entries && $this->hasInUsePresetValue()) {
            $input = $input->withValue('other');
        } elseif (
            ($this->hasFeaturedPresetValue() || $this->hasOutdatedPresetValue()) &&
            in_array($this->getPresetValue()->id(), array_keys($radio_groups))
        ) {
            $input = $input->withValue($this->getPresetValue()->id());
        }

        return $input;
    }

    protected function hasCustomValue(): bool
    {
        return $this->getCustomValue() !== '';
    }

    protected function getCustomValue(): string
    {
        return $this->custom_value;
    }

    protected function hasFeaturedPresetValue(): bool
    {
        return $this->getPresetValue() !== null &&
            $this->getPresetValue()->isFeatured();
    }

    protected function hasInUsePresetValue(): bool
    {
        return $this->getPresetValue() !== null &&
            !$this->getPresetValue()->isFeatured() &&
            !$this->getPresetValue()->isOutdated();
    }

    protected function hasOutdatedPresetValue(): bool
    {
        return $this->getPresetValue() !== null &&
            $this->getPresetValue()->isOutdated();
    }

    protected function getPresetValue(): ?EntryInterface
    {
        if ($this->hasCustomValue()) {
            return null;
        }
        return $this->preset_value ??= $this->repo->getDefaultEntry();
    }
}
