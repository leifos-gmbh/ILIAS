<?php

namespace ImportHandler\I\File\XML\Manifest;

use ImportHandler\File\XML\Manifest\ilExportObjectType;
use ImportHandler\I\File\XML\Manifest\ilHandlerInterface as ilManifestXMLFileHandlerInterface;
use ImportStatus\I\ilHandlerCollectionInterface as ilImportStatusHandlerCollectionInterface;
use ImportHandler\I\File\XML\ilHandlerCollectionInterface as ilXMLFileHandlerCollectionInterface;
use Iterator;
use Countable;

interface ilHandlerCollectionInterface extends Iterator, Countable
{
    public function withMerged(ilHandlerCollectionInterface $other): ilHandlerCollectionInterface;

    public function withElement(ilManifestXMLFileHandlerInterface $element): ilHandlerCollectionInterface;

    public function validateElements(): ilImportStatusHandlerCollectionInterface;

    public function containsExportObjectType(ilExportObjectType $type): bool;

    public function findNextFiles(): ilHandlerCollectioNinterface;

    /**
     * @return ilManifestXMLFileHandlerInterface[]
     */
    public function toArray(): array;

    public function current(): ilManifestXMLFileHandlerInterface;

    public function next(): void;

    public function key(): int;

    public function valid(): bool;

    public function rewind(): void;
}
