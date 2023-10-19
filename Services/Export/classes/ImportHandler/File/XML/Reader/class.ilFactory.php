<?php

namespace ImportHandler\File\XML\Reader;

use ilLogger;
use ImportHandler\I\File\XML\Reader\ilFactoryInterface as ilXMLFileReaderFactoryInterface;
use ImportHandler\I\File\XML\Reader\ilHandlerInterface as ilXMLFileReaderHandlerInterface;
use ImportHandler\File\XML\Reader\ilHandler as ilXMLFileReaderHandler;
use ImportStatus\ilFactory as ilImportStatusFactory;

class ilFactory implements ilXMLFileReaderFactoryInterface
{
    protected ilLogger $logger;

    public function __construct(
        ilLogger $logger
    ) {
        $this->logger = $logger;
    }

    public function handler(): ilXMLFileReaderHandlerInterface
    {
        return new ilXMLFileReaderHandler(
            $this->logger,
            new ilImportStatusFactory()
        );
    }
}
