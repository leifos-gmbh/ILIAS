<?php

namespace ImportStatus;

use ImportStatus\I\ilImportStatusCollectionInterface;
use ImportStatus\I\ilImportStatusInterface;

class ilImportStatusCollection implements ilImportStatusCollectionInterface
{
    /**
     * @var ilImportStatusInterface[]
     */
    private array $status_collection;
    private int $index;
    private int $minIndex;

    /**
     * @param ilImportStatusInterface[] $initial_values
     */
    public function __construct(array $initial_values = [])
    {
        $this->status_collection = $initial_values;
        $this->minIndex = 0;
        $this->index = $this->minIndex;
    }

    /**
     * @return ilImportStatusInterface[]
     */
    private function getArrayOfElementsWithType(StatusType $type): array
    {
        return array_filter(
            $this->toArray(),
            function (ilImportStatus $s) use ($type) {
                return $s->getType() === $type;
            }
        );
    }

    public function hasStatusType(StatusType $type): bool
    {
        return count($this->getArrayOfElementsWithType($type)) > 0;
    }

    public function withStatus(ilImportStatusInterface $import_status): ilImportStatusCollectionInterface
    {
        $clone = clone $this;
        $clone->status_collection[] = $import_status;
        return $clone;
    }

    public function getCollectionOfAllByType(StatusType $type): ilImportStatusCollectionInterface
    {
        return new ilImportStatusCollection($this->getArrayOfElementsWithType($type));
    }

    public function getMergedCollectionWith(ilImportStatusCollectionInterface $other): ilImportStatusCollectionInterface
    {
        return new ilImportStatusCollection(array_merge($this->toArray(), $other->toArray()));
    }

    public function current(): ilImportStatusInterface
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
     * @return ilImportStatusInterface[]
     */
    public function toArray(): array
    {
        return $this->status_collection;
    }
}
