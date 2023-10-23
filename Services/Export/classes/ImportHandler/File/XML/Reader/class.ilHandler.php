<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ImportHandler\File\XML\Reader;

use ilLogger;
use ImportHandler\File\Path\ilHandler as ilParserPathHandlerInterface;
use ImportHandler\I\File\XML\ilHandlerInterface as ilXMLFileHandlerInterface;
use ImportHandler\I\File\XML\Reader\ilHandlerInterface as ilXMLFileReaderHandlerInterface;
use ImportHandler\I\File\XML\Reader\Path\ilFactoryInterface as ilXMLFileReaderPathFactoryInterface;
use ImportHandler\I\File\XSD\ilHandlerInterface as ilXSDFileHandlerInterface;
use ImportStatus\I\ilFactoryInterface as ilImportStatusFactoryInterface;
use ImportStatus\I\ilHandlerCollectionInterface as ilImportStatusHandlerCollectionInterface;
use ImportStatus\StatusType;
use XMLReader;

class ilHandler implements ilXMLFileReaderHandlerInterface
{
    protected ilLogger $logger;
    protected ilImportStatusFactoryInterface $status;
    protected XMLReader $reader;
    protected ilXSDFileHandlerInterface $xsd_file_handler;
    protected ilXMLFileHandlerInterface $xml_file_handler;
    protected string $element_name;

    public function __construct(
        ilLogger $logger,
        ilImportStatusFactoryInterface $status,
    ) {
        $this->logger = $logger;
        $this->status = $status;
        $this->reader = new XMLReader();
    }

    public function withXSDFileHandler(ilXSDFileHandlerInterface $xsd_file): ilXMLFileReaderHandlerInterface
    {
        $clone = clone $this;
        $clone->xsd_file_handler = $xsd_file;
        return $clone;
    }

    public function withXMLFileHandler(ilXMLFileHandlerInterface $xml_file): ilXMLFileReaderHandlerInterface
    {
        $clone = clone $this;
        $clone->xml_file_handler = $xml_file;
        return $clone;
    }

    public function withTargetElement(string $element_name): ilXMLFileReaderHandlerInterface
    {
        $clone = clone $this;
        $clone->element_name = $element_name;
        return $clone;
    }

    protected function handleErrors(): ilImportStatusHandlerCollectionInterface
    {
        $status_collection = $this->status->handlerCollection();
        foreach (libxml_get_errors() as $error) {
            $status_collection->withAddedStatus(
                $this->status->handler()
                    ->withType(StatusType::FAILED)
                    ->withContent($this->status->content()->builder()->string()
                        ->withString("ERROR: " . $error->message))
            );
        }
        libxml_clear_errors();
        return $status_collection;
    }

    public function validate(): ilImportStatusHandlerCollectionInterface
    {
        $old_val = libxml_use_internal_errors(true);
        $this->reader->open($this->xml_file_handler->getFilePath());
        $status_collection = $this->handleErrors();
        if ($status_collection->hasStatusType(StatusType::FAILED)) {
            return $status_collection;
        }
        if (isset($this->element_name) && !$this->moveToElement()) {
            libxml_use_internal_errors($old_val);
            return $status_collection->withAddedStatus(
                $this->status->handler()
                    ->withType(StatusType::FAILED)
                    ->withContent($this->status->content()->builder()->string()
                        ->withString("Element not found in file: " . $this->element_name))
            );
        }
        $this->reader->setSchema($this->xsd_file_handler->getFilePath());
        while ($this->reader->read()) {
            if (isset($this->element_name)) {

            }
        }
        $status_collection = $this->handleErrors();
        if ($status_collection->hasStatusType(StatusType::FAILED)) {
            return $status_collection;
        }
        libxml_use_internal_errors($old_val);
        return $status_collection;
    }

    protected function moveToElement(): bool
    {
        while ($this->reader->read()) {
            if(
                $this->reader->nodeType === XMLReader::ELEMENT &&
                $this->reader->name === $this->element_name
            ) {
                return true;
            }
        }
        return false;
    }
}
