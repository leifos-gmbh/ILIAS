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

namespace ImportHandler\File\Validation;

use ilLogger;
use ImportHandler\I\File\ilHandlerInterface as ilFileHandlerInterface;
use ImportHandler\I\File\Path\ilFactoryInterface as ilParserPathFactoryInterface;
use ImportHandler\I\File\Path\ilHandlerInterface as ilParserPathHandlerInterface;
use ImportHandler\I\File\Validation\ilStreamHandlerInterface as ilXMLStreamFileValidationHandlerInterface;
use ImportHandler\I\File\XML\ilHandlerInterface as ilXMLFileHandlerInterface;
use ImportHandler\I\File\XML\Reader\Path\ilFactoryInterface as ilXMLFileReaderPathFactoryInterface;
use ImportHandler\I\File\XSD\ilHandlerInterface as ilXSDFileHandlerInterface;
use ImportHandler\I\Parser\ilHandlerInterface as ilParserHandlerInterface;
use ImportStatus\I\ilFactoryInterface as ilImportStatusFactoryInterface;
use ImportStatus\I\ilHandlerCollectionInterface as ilImportStatusHandlerCollectionInterface;
use ImportStatus\I\ilHandlerInterface as ilImportStatusHandlerInterface;
use ImportStatus\StatusType;
use LibXMLError;
use XMLReader;

class ilStreamHandler implements ilXMLStreamFileValidationHandlerInterface
{
    protected const TMP_DIR_NAME = 'temp';
    protected const XML_DIR_NAME = 'xml';

    protected ilLogger $logger;
    protected ilImportStatusFactoryInterface $import_status;
    protected ilParserHandlerInterface $parser_handler;
    protected ilParserPathFactoryInterface $path;
    protected ilImportStatusHandlerInterface $success_status;
    protected ilXMLFileReaderPathFactoryInterface $reader_path;

    public function __construct(
        ilLogger $logger,
        ilParserHandlerInterface $parser_handler,
        ilImportStatusFactoryInterface $import_status,
        ilParserPathFactoryInterface $path,
        ilXMLFileReaderPathFactoryInterface $reader_path
    ) {
        $this->logger = $logger;
        $this->import_status = $import_status;
        $this->parser_handler = $parser_handler;
        $this->path = $path;
        $this->success_status = $import_status->handler()
            ->withType(StatusType::SUCCESS)
            ->withContent($import_status->content()->builder()->string()->withString('Validation SUCCESS'));
        $this->reader_path = $reader_path;
    }

    /**
     * @param ilFileHandlerInterface $file_handlers
     */
    protected function checkIfFilesExist(array $file_handlers): ilImportStatusHandlerCollectionInterface
    {
        $status_collection = $this->import_status->handlerCollection()->withNumberingEnabled(true);
        foreach ($file_handlers as $file_handler) {
            if($file_handler->fileExists()) {
                continue;
            }
            $status_collection->withAddedStatus($this->import_status->handler()
                ->withType(StatusType::FAILED)
                ->withContent($this->import_status->content()->builder()->string()
                    ->withString('File does not exist: ' . $file_handler->getFilePath())));
        }
        return $status_collection;
    }

    /**
     * @param LibXMLError[] $errors
     */
    protected function collectErrors(
        ?ilXMLFileHandlerInterface $xml_file_handler = null,
        ?ilXSDFileHandlerInterface $xsd_file_handler = null,
        array $errors = []
    ): ilImportStatusHandlerCollectionInterface {
        $status_collection = $this->import_status->handlerCollection();
        foreach ($errors as $error) {
            $status_collection = $status_collection->getMergedCollectionWith(
                $this->createErrorMessage($error->message, $xml_file_handler, $xsd_file_handler)
            );
        }
        return $status_collection;
    }

    protected function createErrorMessage(
        string $msg,
        ?ilXMLFileHandlerInterface $xml_file_handler = null,
        ?ilXSDFileHandlerInterface $xsd_file_handler = null
    ): ilImportStatusHandlerCollectionInterface {
        $status_collection = $this->import_status->handlerCollection();
        $xml_str = is_null($xml_file_handler)
            ? ''
            : "<br>XML-File: " . $xml_file_handler->getSubPathToDirBeginningAtPathEnd(self::TMP_DIR_NAME);
        $xsd_str = is_null($xsd_file_handler)
            ? ''
            : "<br>XSD-File: " . $xsd_file_handler->getSubPathToDirBeginningAtPathEnd(self::XML_DIR_NAME);
        $content = $this->import_status->content()->builder()->string()->withString(
            "Validation FAILED"
            . $xml_str
            . $xsd_str
            . "<br>ERROR Message: " . $msg
        );
        $status_collection = $status_collection->withAddedStatus(
            $this->import_status->handler()->withType(StatusType::FAILED)->withContent($content)
        );
        return $status_collection;
    }

    public function validateXMLFile(
        ilXMLFileHandlerInterface $xml_file_handler,
        ilXSDFileHandlerInterface $xsd_file_handler
    ): ilImportStatusHandlerCollectionInterface {
        $path = $this->path->handler()->withNode($this->path->node()->anyElement())->withStartAtRoot(true);
        return $this->validateXMLAtPath($xml_file_handler, $xsd_file_handler, $path);
    }

    public function validateXMLAtPath(
        ilXMLFileHandlerInterface $xml_file_handler,
        ilXSDFileHandlerInterface $xsd_file_handler,
        ilParserPathHandlerInterface $path_handler
    ): ilImportStatusHandlerCollectionInterface {
        $this->logger->debug(
            "\n\nValidating:"
            . "\nXML: " . $xml_file_handler->getFilePath()
            . "\nXSD: " . $xsd_file_handler->getFilePath() . "\n"
        );
        // Enable manual error handling
        $old_val = libxml_use_internal_errors(true);
        $reader = new XMLReader();
        $reader->open($xml_file_handler->getFilePath());
        // Handle errors, on initialization
        $errors = libxml_get_errors();
        $status_collection = $this->collectErrors($xml_file_handler, $xsd_file_handler, $errors);
        libxml_clear_errors();
        if ($status_collection->hasStatusType(StatusType::FAILED)) {
            libxml_use_internal_errors($old_val);
            return $status_collection;
        }
        // Move reader
        $reader_path_handler = $this->reader_path->handler()->withPath($path_handler);
        while ($reader_path_handler->continueAlongPath($reader));
        if(!$reader_path_handler->finished()) {
            return $this->createErrorMessage(
                'Path not found in xml: ' . $path_handler->toString(),
                $xml_file_handler,
                $xsd_file_handler
            );
        }
        // Validate
        $reader->setSchema($xsd_file_handler->getFilePath());
        while ($reader->read());

        $errors = libxml_get_errors();
        libxml_clear_errors();
        $status_collection = $this->import_status->handlerCollection()->getMergedCollectionWith($this->collectErrors(
            $xml_file_handler,
            $xsd_file_handler,
            $errors
        ));
        // Restore old value of manual error handling
        libxml_use_internal_errors($old_val);
        return $status_collection->hasStatusType(StatusType::FAILED)
            ? $status_collection
            : $this->import_status->handlerCollection()->withAddedStatus($this->success_status);
    }
}
