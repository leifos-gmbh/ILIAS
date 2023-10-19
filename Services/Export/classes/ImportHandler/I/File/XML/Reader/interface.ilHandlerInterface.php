<?php

namespace ImportHandler\I\File\XML\Reader;

use ImportHandler\I\File\XML\ilHandlerInterface as ilXMLFileHandlerInterface;
use ImportHandler\I\File\XSD\ilHandlerInterface as ilXSDFileHandlerInterface;
use ImportHandler\Parser\Path\ilHandler as ilParserPathHandlerInterface;
use ImportStatus\I\ilHandlerCollectionInterface as ilImportStatusHandlerCollectionInterface;

interface ilHandlerInterface
{
    public function withXSDFileHandler(ilXSDFileHandlerInterface $xsd_file): ilHandlerInterface;

    public function withXMLFileHandler(ilXMLFileHandlerInterface $xml_file): ilHandlerInterface;

    public function moveAlongPath(ilParserPathHandlerInterface $path_handler): ilHandlerInterface;

    public function debug(string $msg);
}
