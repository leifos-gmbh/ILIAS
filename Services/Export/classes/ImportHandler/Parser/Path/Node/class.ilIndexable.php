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

namespace ImportHandler\Parser\Path\Node;

use ImportHandler\I\Parser\Path\ilComparisonInterface;
use ImportHandler\I\Parser\Path\Node\ilIndexableInterface;
use ImportHandler\Parser\Path\ilComparisonDummy;

class ilIndexable implements ilIndexableInterface
{
    protected ilComparisonInterface $comparison;
    protected string $node_name;
    protected int $index;
    protected bool $indexing_from_end_enabled;

    public function __construct()
    {
        $this->comparison = new ilComparisonDummy();
        $this->node_name = '';
        $this->index = 0;
        $this->indexing_from_end_enabled = false;
    }

    public function withName(string $node_name): ilIndexableInterface
    {
        $clone = clone $this;
        $clone->node_name = $node_name;
        return $clone;
    }

    public function withIndex(int $index): ilIndexableInterface
    {
        $clone = clone $this;
        $clone->index = $index;
        return $clone;
    }

    public function withComparison(ilComparisonInterface $comparison): ilIndexableInterface
    {
        $clone = clone $this;
        $clone->comparison = $comparison;
        return $clone;
    }

    public function withIndexingFromEndEnabled(bool $enabled): ilIndexableInterface
    {
        $clone = clone $this;
        $clone->indexing_from_end_enabled = $enabled;
        return $clone;
    }

    public function toString(): string
    {
        $indexing = '';

        if ($this->comparison instanceof ilComparisonDummy) {
            $indexing = $this->indexing_from_end_enabled
                ? '(last)-' . $this->index
                : $this->index;
        } else {
            $indexing = 'position()' . $this->comparison->toString();
        }

        return $this->node_name . '[' . $indexing . ']';
    }
}
