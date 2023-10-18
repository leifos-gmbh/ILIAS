<?php

namespace ImportHandler\File\Validation;

use ilLogger;
use ImportHandler\I\File\ilHandlerInterface as ilFileHandlerInterface;
use ImportHandler\I\File\Validation\ilHandlerInterface as ilFileValidationHandlerInterface;
use ImportHandler\I\File\XML\ilHandlerInterface as ilXMLFileHandlerInterface;
use ImportHandler\I\File\XSD\ilHandlerInterface as ilXSDFileHandlerInterface;
use ImportHandler\I\Parser\ilHandlerInterface as ilParserHandlerInterface;
use ImportHandler\I\Parser\Path\ilFactoryInterface as ilParserPathFactoryInterface;
use ImportHandler\I\Parser\Path\ilHandlerInterface as ilParserPathHandlerInterface;
use ImportHandler\I\Parser\XML\Node\ilInfoCollectionInterface as ilParserXMLNodeInfoCollectionInterface;
use ImportHandler\I\Parser\Path\Node\ilSimpleInterface as ilParserPathNodeSimpleInterface;
use ImportStatus\I\ilFactoryInterface as ilImportStatusFactoryInterface;
use ImportStatus\I\ilHandlerCollectionInterface as ilImportStatusHandlerCollectionInterface;
use ImportStatus\I\ilHandlerInterface as ilImportStatusHandlerInterface;
use ImportStatus\StatusType;
use LibXMLError;
use XMLReader;

class ilStreamHandler implements ilFileValidationHandlerInterface
{
    protected const TMP_DIR_NAME = 'temp';
    protected const XML_DIR_NAME = 'xml';

    protected ilLogger $logger;
    protected ilImportStatusFactoryInterface $import_status;
    protected ilParserHandlerInterface $parser_handler;
    protected ilParserPathFactoryInterface $path;
    protected ilImportStatusHandlerInterface $success_status;

    public function __construct(
        ilLogger $logger,
        ilParserHandlerInterface $parser_handler,
        ilImportStatusFactoryInterface $import_status,
        ilParserPathFactoryInterface $path
    ) {
        $this->logger = $logger;
        $this->import_status = $import_status;
        $this->parser_handler = $parser_handler;
        $this->path = $path;
        $this->success_status = $import_status->handler()
            ->withType(StatusType::SUCCESS)
            ->withContent($import_status->content()->builder()->string()->withString('Validation SUCCESS'));
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
        $status_collection = $this->import_status->handlerCollection()->withNumberingEnabled(true);
        $xml_str = is_null($xml_file_handler)
            ? ''
            : "<br>XML-File: " . $xml_file_handler->getSubPathToDirBeginningAtPathEnd(self::TMP_DIR_NAME);
        $xsd_str = is_null($xsd_file_handler)
            ? ''
            : "<br>XSD-File: " . $xsd_file_handler->getSubPathToDirBeginningAtPathEnd(self::XML_DIR_NAME);
        foreach ($errors as $error) {
            $content = $this->import_status->content()->builder()->string()->withString(
                "Validation FAILED"
                . $xml_str
                . $xsd_str
                . "<br>ERROR Message: " . $error->message
            );
            $status_collection = $status_collection->withAddedStatus(
                $this->import_status->handler()->withType(StatusType::FAILED)->withContent($content)
            );
        }
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
        $old_value_of_use_internal_errors = libxml_use_internal_errors(true);
        // Open stream and collect errors
        $reader = new XMLReader();
        $reader->open($xml_file_handler->getFilePath());
        //$reader->setSchema($xsd_file_handler->getFilePath());
        $component_name = null;

        $this->moveXMLReader($reader, $path_handler);
        // TODO
        $reader->close();
        $errors = libxml_get_errors();
        libxml_clear_errors();
        $status_collection = $this->import_status->handlerCollection()->getMergedCollectionWith($this->collectErrors(
            $xml_file_handler,
            $xsd_file_handler,
            $errors
        ));
        // Restore old value of manual error handling
        libxml_use_internal_errors($old_value_of_use_internal_errors);
        return $status_collection->hasStatusType(StatusType::FAILED)
            ? $status_collection
            : $this->import_status->handlerCollection()->withAddedStatus($this->success_status);
    }

    protected function moveXMLReader(XMLReader $reader, ilParserPathHandlerInterface $path_handler): bool
    {
        $reached_path_end = false;
        $reached_file_end = false;
        while (!$reached_path_end && !$reached_file_end) {
            $path_node = $path_handler->firstElement();
            $path_handler = $path_handler->subPath(1);

            $msg = "\n\n\nStream Reading:";
            $msg .= "\n      path node: " . $path_node->toString();
            while (!($reached_file_end = !$reader->read())) {
                $msg .= "\n    reader name: " . $reader->name;
                if ($reader->name === $path_node->toString()) {
                    break;
                }
            }
            $msg .= "\n\n";
            $this->logger->debug($msg);

            $reached_path_end = $path_handler->count() === 0;
        }
        return !$reached_file_end;
    }
}
