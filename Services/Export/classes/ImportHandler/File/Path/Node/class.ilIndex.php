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

namespace ImportHandler\File\Path\Node;

use ImportHandler\I\File\Path\Node\ilIndexInterface as ilIndexFilePathNodeInterface;
use ImportHandler\File\Path\ilComparisonDummy;
use ImportHandler\I\File\Path\ilComparisonInterface;
use XMLReader;

class ilIndex implements ilIndexFilePathNodeInterface
{
    protected ilComparisonInterface $comparison;
    protected int $index;
    protected bool $indexing_from_end_enabled;

    public function __construct()
    {
        $this->comparison = new ilComparisonDummy();
        $this->index = 0;
        $this->indexing_from_end_enabled = false;
    }

    public function withIndex(int $index): ilIndexFilePathNodeInterface
    {
        $clone = clone $this;
        $clone->index = $index;
        return $clone;
    }

    public function withComparison(ilComparisonInterface $comparison): ilIndexFilePathNodeInterface
    {
        $clone = clone $this;
        $clone->comparison = $comparison;
        return $clone;
    }

    public function withIndexingFromEndEnabled(bool $enabled): ilIndexFilePathNodeInterface
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

        return '[' . $indexing . ']';
    }

    public function requiresPathSeparator(): bool
    {
        return false;
    }
}
