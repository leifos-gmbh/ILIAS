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

namespace ILIAS\Calendar\Recurrence;

use ILIAS\UI\Component\Input\Field\Group;

interface InputBuilder
{
    public function withoutUnlimitedRecurrences(bool $without = true): InputBuilder;

    public function withoutDaily(bool $without = true): InputBuilder;

    public function withoutWeekly(bool $without = true): InputBuilder;

    public function withoutMonthly(bool $without = true): InputBuilder;

    public function withoutYearly(bool $without = true): InputBuilder;

    public function hasUnlimitedRecurrences(): bool;

    public function hasDaily(): bool;

    public function hasWeekly(): bool;

    public function hasMonthly(): bool;

    public function hasYearly(): bool;

    public function get(): Group;
}
