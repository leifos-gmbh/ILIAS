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

declare(strict_types=0);

namespace ILIAS\Tracking\PersonalLearningProgress\DateConstraint;

use ilDateTime;
use ILIAS\Tracking\PersonalLearningProgress\I\DateConstraint\HandlerInterface as PLPDateConstraintInteface;

class Handler implements PLPDateConstraintInteface
{
    protected ilDateTime|null $lower_bound;
    protected ilDateTime|null $upper_bound;

    public function __construct()
    {
        $this->upper_bound = null;
        $this->lower_bound = null;
    }

    public function withLowerBound(
        ilDateTime|null $lower_bound
    ): PLPDateConstraintInteface {
        $clone = clone $this;
        $clone->lower_bound = $lower_bound;
        return $clone;
    }

    public function withUpperBound(
        ilDateTime|null $upper_bound
    ): PLPDateConstraintInteface {
        $clone = clone $this;
        $clone->upper_bound = $upper_bound;
        return $clone;
    }

    public function getLowerBound(): ilDateTime|null
    {
        return $this->lower_bound;
    }

    public function getUpperBound(): ilDateTime|null
    {
        return $this->upper_bound;
    }

    public function isInBounds(
        ilDateTime $date
    ): bool {
        $is_above_lower = !$this->isBelowLowerBound($date);
        $is_below_upper = !$this->isAboveUpperBound($date);
        return $is_above_lower && $is_below_upper;
    }

    public function isBelowLowerBound(
        ilDateTime $date
    ): bool {
        return !is_null($this->lower_bound) ? ilDateTime::_before($date, $this->lower_bound) : false;
    }

    public function isAboveUpperBound(
        ilDateTime $date
    ): bool {
        return !is_null($this->upper_bound) ? ilDateTime::_after($date, $this->upper_bound) : false;
    }
}
