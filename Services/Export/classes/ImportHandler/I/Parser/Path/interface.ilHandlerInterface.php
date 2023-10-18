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

namespace ImportHandler\I\Parser\Path;

use ImportHandler\I\Parser\Path\Node\ilNodeInterface as ilParserPathNodeInterface;
use Iterator;
use Countable;

interface ilHandlerInterface extends Iterator, Countable
{
    public function withStartAtRoot(bool $enabled): ilHandlerInterface;

    public function withNode(ilParserPathNodeInterface $node): ilHandlerInterface;

    public function toString(): string;

    public function subPath(int $start, ?int $end = null): ilHandlerInterface;

    public function firstElement(): ilParserPathNodeInterface|null;

    public function lastElement(): ilParserPathNodeInterface|null;

    /**
     * @return ilParserPathNodeInterface[]
     */
    public function toArray(): array;

    public function current(): ilParserPathNodeInterface;

    public function next(): void;

    public function key(): int;

    public function valid(): bool;

    public function rewind(): void;

    public function count(): int;
}
