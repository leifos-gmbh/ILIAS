<?php

namespace ImportHandler\File\XML\Reader\Path;

use ImportHandler\I\File\Path\ilHandlerInterface as ilXMLFilePathHandlerInterface;
use ImportHandler\I\File\XML\Reader\Path\ilHandlerInterface as ilXMLFileReaderPathHandlerInterface;
use XMLReader;

class ilHandler implements ilXMLFileReaderPathHandlerInterface
{
    protected ilXMLFilePathHandlerInterface $path_handler;

    public function continueAlongPath(XMLReader $reader): bool
    {
        $current_node = $this->path_handler->firstElement();
        $this->path_handler = $this->path_handler->subPath(1, $this->path_handler->count() - 1);
        return $current_node->moveReader($reader);
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
