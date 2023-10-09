<?php

namespace ImportStatus\I;

use Countable;
use ImportStatus\StatusType;
use Iterator;

interface ilImportStatusCollectionInterface extends Iterator, Countable, Arrayable
{
    public function hasStatusType(StatusType $type): bool;

    public function withStatus(ilImportStatusInterface $import_status): ilImportStatusCollectionInterface;

    public function getCollectionOfAllByType(StatusType $type): ilImportStatusCollectionInterface;

    public function getMergedCollectionWith(ilImportStatusCollectionInterface $other): ilImportStatusCollectionInterface;

    public function current(): ilImportStatusInterface;

    public function next(): void;

    public function key(): int;

    public function valid(): bool;

    public function rewind(): void;

    public function count(): int;

    /**
     * @return ilImportStatusInterface[]
     */
    public function toArray(): array;
}
