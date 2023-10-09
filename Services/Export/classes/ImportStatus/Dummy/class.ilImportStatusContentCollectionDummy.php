<?php

namespace ImportStatus\Dummy;

use ImportStatus\I\ilImportStatusCollectionInterface;
use ImportStatus\I\ilImportStatusInterface;
use ImportStatus\StatusType;

class ilImportStatusContentCollectionDummy implements ilImportStatusCollectionInterface
{
    public function hasStatusType(StatusType $type): bool
    {
        return $type === StatusType::DUMMY;
    }

    public function withAddedStatus(ilImportStatusInterface $import_status): ilImportStatusCollectionInterface
    {
        return new ilImportStatusContentCollectionDummy();
    }

    public function getCollectionOfAllByType(StatusType $type): ilImportStatusCollectionInterface
    {
        return new ilImportStatusContentCollectionDummy();
    }

    public function getMergedCollectionWith(ilImportStatusCollectionInterface $other): ilImportStatusCollectionInterface
    {
        return new ilImportStatusContentCollectionDummy();
    }

    public function current(): ilImportStatusInterface
    {
        return new ilImportStatusDummy();
    }

    public function next(): void
    {
    }

    public function key(): int
    {
        return 0;
    }

    public function valid(): bool
    {
        return false;
    }

    public function rewind(): void
    {
    }

    public function count(): int
    {
        return 0;
    }

    public function toArray(): array
    {
        return [];
    }
}
