<?php

namespace ImportHandler\File\XML\Reader\Path;

use ImportHandler\I\File\XML\Reader\Path\ilFactoryInterface as ilXMLFileReaderPathFactoryInterface;
use ImportHandler\I\File\XML\Reader\Path\ilHandlerInterface as ilXMLFileReaderPathHandlerInterface;
use ImportHandler\File\XML\Reader\Path\ilHandler as ilXMLFileReaderPathHandler;

class ilFactory implements ilXMLFileReaderPathFactoryInterface
{
    public function handler(): ilXMLFileReaderPathHandlerInterface
    {
        return new ilXMLFileReaderPathHandler();
    }
}
