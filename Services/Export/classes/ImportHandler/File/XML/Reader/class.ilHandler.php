<?php

namespace ImportHandler\File\XML\Reader;

use ilLogger;
use ImportHandler\I\File\XML\ilHandlerInterface as ilXMLFileHandlerInterface;
use ImportHandler\I\File\XML\Reader\ilHandlerInterface as ilXMLFileReaderHandlerInterface;
use ImportHandler\I\File\XSD\ilHandlerInterface as ilXSDFileHandlerInterface;
use ImportHandler\Parser\Path\ilHandler as ilParserPathHandlerInterface;
use ImportStatus\I\ilHandlerCollectionInterface as ilImportStatusHandlerCollectionInterface;
use ImportStatus\I\ilFactoryInterface as ilImportStatusFactoryInterface;
use XMLReader;

class ilHandler implements ilXMLFileReaderHandlerInterface
{
    protected ilLogger $logger;
    protected ilImportStatusFactoryInterface $status;
    protected XMLReader $reader;

    public function __construct(
        ilLogger $logger,
        ilImportStatusFactoryInterface $status
    ) {
        $this->logger = $logger;
        $this->status = $status;
        $this->reader = new XMLReader();
    }

    public function withXSDFileHandler(ilXSDFileHandlerInterface $xsd_file): ilXMLFileReaderHandlerInterface
    {
        $clone = clone $this;
        $clone->reader->setSchema($xsd_file->getFilePath());
        return $clone;
    }

    public function withXMLFileHandler(ilXMLFileHandlerInterface $xml_file): ilXMLFileReaderHandlerInterface
    {
        $clone = clone $this;
        $clone->reader->open($xml_file->getFilePath());
        return $clone;
    }

    public function moveAlongPath(ilParserPathHandlerInterface $path_handler): ilXMLFileReaderHandlerInterface
    {
        $clone = clone $this;
        $reached_path_end = false;
        $reached_file_end = false;
        while (!$reached_path_end && !$reached_file_end) {
            $path_node = $path_handler->firstElement();
            $path_handler = $path_handler->subPath(1);
            $msg = "\n\n\nStream Reading:";
            $msg .= "\n      path node: " . $path_node->toString();
            while (!($reached_file_end = !$clone->reader->read())) {
                $msg .= "\n    reader name: " . $clone->reader->name;
                if ($clone->reader->name === $path_node->toString()) {
                    break;
                }
            }
            $msg .= "\n\n";
            $clone->logger->debug($msg);
            $reached_path_end = $path_handler->count() === 0;
        }
        return $clone;
    }

    public function debug(string $msg)
    {
        $content = "";
        while (!($reached_file_end = !$this->reader->read())) {
            $content .= "\n    >>>: " . $this->reader->name;
        }
        $this->logger->debug(
            "\n\n\n"
            . $msg
            . $content
            . "\n\n"
        );
    }
}
