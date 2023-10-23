<?php

namespace ImportHandler\File\XML\Reader\Path;

use ilLogger;
use ImportHandler\I\File\XML\Reader\Path\ilFactoryInterface as ilXMLFileReaderPathFactoryInterface;
use ImportHandler\I\File\XML\Reader\Path\ilHandlerInterface as ilXMLFileReaderPathHandlerInterface;
use ImportHandler\File\XML\Reader\Path\ilHandler as ilXMLFileReaderPathHandler;

class ilFactory implements ilXMLFileReaderPathFactoryInterface
{
    protected ilLogger $logger;

    public function __construct(ilLogger $logger)
    {
        $this->logger = $logger;
    }

    public function handler(): ilXMLFileReaderPathHandlerInterface
    {
        return new ilXMLFileReaderPathHandler($this->logger);
    }
}
