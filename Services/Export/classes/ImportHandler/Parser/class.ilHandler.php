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

namespace ImportHandler\Parser;

use DOMAttr;
use DOMDocument;
use DOMNode;
use DOMXPath;
use ilImportException;
use ilLogger;
use ImportHandler\File\XML\ilHandler as ilXMLFileHandler;
use ImportHandler\I\File\ilHandlerInterface as ilFileHandlerInterface;
use ImportHandler\I\File\XML\ilHandlerInterface as ilXMLFileHandlerInterface;
use ImportHandler\I\Parser\ilHandlerInterface as ilParseHandlerInterface;
use ImportHandler\I\Parser\Path\ilHandlerInterface as ilParserPathHandlerInterface;
use ImportHandler\I\Parser\XML\Node\ilInfoCollectionInterface as ilParserXMLNodeInfoCollectionInterface;
use ImportHandler\I\Parser\XML\Node\ilFactoryInterface as ilParserXMLNodeFactory;
use ImportHandler\I\File\ilFactoryInterface as ilFileFactory;
use SplFileInfo;
use XMLReader;

class ilHandler implements ilParseHandlerInterface
{
    protected ilXMLFileHandlerInterface $xml_file_handler;
    protected ilParserXMLNodeFactory $xml_node;
    protected ilLogger $logger;
    protected DOMDocument $dom_doc;

    public function __construct(
        ilLogger $logger,
        ilParserXMLNodeFactory $xml_node_factory,
    ) {
        $this->logger = $logger;
        $this->xml_node = $xml_node_factory;
    }

    protected function checkIfFileHandlerIsSet(): void
    {
        if(!isset($this->xml_file_handler)) {
            throw new ilImportException('XMLFileHandler not set.');
        }
    }

    public function withFileHandler(ilXMLFileHandlerInterface $file_handler): ilParseHandlerInterface
    {
        $clone = clone $this;
        $clone->xml_file_handler = $file_handler;
        $clone->dom_doc = $file_handler->loadDomDocument();
        return $clone;
    }

    public function getNodeInfoAt(ilParserPathHandlerInterface $path): ilParserXMLNodeInfoCollectionInterface
    {
        $log_msg = "\n\n\nGetting node info at path: " . $path->toString();
        $log_msg .= "\nFrom file: " . $this->xml_file_handler->getFilePath();
        $this->checkIfFileHandlerIsSet();
        $dom_xpath = new DOMXPath($this->dom_doc);
        $nodes = $dom_xpath->query($path->toString());
        $node_info_collection = $this->xml_node->infoCollection();
        /** @var DOMNode $node **/
        foreach ($nodes as $node) {
            $log_msg .= "\nFound Node: " . $node->nodeName;
            /** @var DOMAttr $attribute */
            foreach ($node->attributes as $attribute) {
                $log_msg .= "\nWith Attribute: " . $attribute->name . " = " . $attribute->value;
            }
            $node_info = $this->xml_node->info()->withDOMNode($node);
            $node_info_collection = $node_info_collection->withElement($node_info);
        }
        $log_msg .= "\n\n";
        $this->logger->debug($log_msg);
        return $node_info_collection;
    }

    public function moveXMLReader(XMLReader $reader, ilParserPathHandlerInterface $path_handler): bool
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
