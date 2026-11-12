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

namespace ILIAS\MetaData\Services\CopyrightHelper\Input;

use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\MetaData\Copyright\Input\Builder;

class InputBuilder implements InputBuilderInterface
{
    public function __construct(
        protected Builder $internal_builder
    ) {
    }

    public function get(): FormInput
    {
        return $this->internal_builder->get();
    }

    public function withCustomInput(): InputBuilderInterface
    {
        $clone = clone $this;
        $clone->internal_builder = $this->internal_builder->withCustomInput();
        return $clone;
    }

    public function withNonFeaturedEntries(): InputBuilderInterface
    {
        $clone = clone $this;
        $clone->internal_builder = $this->internal_builder->withNonFeaturedEntries();
        return $clone;
    }
}
