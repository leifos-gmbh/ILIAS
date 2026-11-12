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
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\MetaData\Copyright\EntryInterface;

interface BuilderInterface
{
    public function get(): FormInput;

    public function withCustomValue(string $value): BuilderInterface;

    public function withPresetValue(EntryInterface $value): BuilderInterface;

    public function withCustomInput(): BuilderInterface;

    public function withNonFeaturedEntries(): BuilderInterface;
}
