<?php

namespace ImportHandler\File\XML\Reader\Path;

use ilLogger;
use ImportHandler\I\File\Path\ilHandlerInterface as ilXMLFilePathHandlerInterface;
use ImportHandler\I\File\XML\Reader\Path\ilHandlerInterface as ilXMLFileReaderPathHandlerInterface;
use XMLReader;

class ilHandler implements ilXMLFileReaderPathHandlerInterface
{
    protected ilXMLFilePathHandlerInterface $path_handler;
    protected ilLogger $logger;

    public function __construct(ilLogger $logger)
    {
        $this->logger = $logger;
    }

    public function continueAlongPath(XMLReader $reader): bool
    {
        $current_node = $this->path_handler->firstElement();
        $this->path_handler = $this->path_handler->subPath(1, $this->path_handler->count());
        $status = $current_node->moveReader($reader);

        $this->logger->debug(
            "\n\n"
            . "\nCurrent Node: " . $current_node->toString()
            . "\nFound?: " . ($status ? 'YES' : 'NO')
            . "\nRemaining Path: " . $this->path_handler->toString()
        );

        return $status;
    }

    public function withPath(ilXMLFilePathHandlerInterface $path_handler): ilXMLFileReaderPathHandlerInterface
    {
        $clone = clone $this;
        $clone->path_handler = $path_handler;
        return $clone;
    }

    public function finished(): bool
    {
        return $this->path_handler->count() === 0;
    }
}
