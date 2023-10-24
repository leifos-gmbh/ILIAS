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

namespace ImportStatus;

use ImportStatus\I\ilCollectionInterface;
use ImportStatus\I\ilHandlerInterface;

class ilCollection implements ilCollectionInterface
{
    /**
     * @var ilHandlerInterface[]
     */
    protected array $status_collection;
    protected int $index;
    protected int $minIndex;
    protected bool $is_numbering_enabled;

    /**
     * @param ilHandlerInterface[] $initial_values
     */
    public function __construct(array $initial_values = [])
    {
        $this->status_collection = $initial_values;
        $this->minIndex = 0;
        $this->index = $this->minIndex;
        $this->is_numbering_enabled = false;
    }

    /**
     * @return ilHandlerInterface[]
     */
    protected function getArrayOfElementsWithType(StatusType $type): array
    {
        return array_filter(
            $this->toArray(),
            function (ilHandler $s) use ($type) {
                return $s->getType() === $type;
            }
        );
    }

    public function hasStatusType(StatusType $type): bool
    {
        return count($this->getArrayOfElementsWithType($type)) > 0;
    }

    public function withAddedStatus(ilHandlerInterface $import_status): ilCollection
    {
        $clone = clone $this;
        $clone->status_collection[] = $import_status;
        return $clone;
    }

    public function getCollectionOfAllByType(StatusType $type): ilCollectionInterface
    {
        return new ilCollection($this->getArrayOfElementsWithType($type));
    }

    public function getMergedCollectionWith(ilCollectionInterface $other): ilCollectionInterface
    {
        return new ilCollection(array_merge($this->toArray(), $other->toArray()));
    }

    public function current(): ilHandlerInterface
    {
        return $this->status_collection[$this->index];
    }

    public function next(): void
    {
        $this->index++;
    }

    public function key(): int
    {
        return $this->index;
    }

    public function valid(): bool
    {
        return $this->minIndex <= $this->index && $this->index < $this->count();
    }

    public function rewind(): void
    {
        $this->index = $this->minIndex;
    }

    public function count(): int
    {
        return count($this->status_collection);
    }

    /**
     * @return ilHandlerInterface[]
     */
    public function toArray(): array
    {
        return $this->status_collection;
    }

    public function withNumberingEnabled(bool $enabled): ilCollectionInterface
    {
        $clone = clone $this;
        $clone->is_numbering_enabled = $enabled;
        return $clone;
    }

    public function toString(StatusType ...$types): string
    {
        $collection = new ilCollection();
        $msg = "<br>Listing status messages (of type(s)";
        foreach ($types as $type) {
            $msg .= " " . $type->name;
            $collection = $collection->getMergedCollectionWith($this->getCollectionOfAllByType($type));
        }
        $msg .= "):<br>";
        $elements = $collection->toArray();
        for($i = 0; $i < count($elements); $i++) {
            $faied_status = $elements[$i];
            $msg .= "<br>";
            $msg .= $this->is_numbering_enabled
                ? '[' . $i . ']: '
                : '';
            $msg .= $faied_status->getContent()->toString();
        }
        return $msg;
    }
}
