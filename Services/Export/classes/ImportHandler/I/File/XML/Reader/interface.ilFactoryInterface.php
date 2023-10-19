<?php

namespace ImportHandler\I\File\XML\Reader;

use ImportHandler\I\File\XML\Reader\ilHandlerInterface as ilXMLFileReaderHandlerInterface;

interface ilFactoryInterface
{
    public function handler(): ilXMLFileReaderHandlerInterface;
}
