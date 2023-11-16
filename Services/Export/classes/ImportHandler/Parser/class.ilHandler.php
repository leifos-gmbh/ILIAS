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
use ilLogger;
use ImportHandler\I\File\Path\ilHandlerInterface as ilFilePathHandlerInterface;
use ImportHandler\I\File\XML\ilHandlerInterface as ilXMLFileHandlerInterface;
use ImportHandler\I\File\XML\Node\Info\ilCollectionInterface as ilXMLFileNodeInfoCollectionInterface;
use ImportHandler\I\File\XML\Node\Info\ilFactoryInterface as ilXMLFileNodeInfoFactoryInterface;
use ImportHandler\I\Parser\ilHandlerInterface as ilParseHandlerInterface;
use ImportStatus\Exception\ilException as ilImportStatusException;

class ilHandler implements ilParseHandlerInterface
{
    protected ilXMLFileHandlerInterface $xml_file_handler;
    protected ilXMLFileNodeInfoFactoryInterface $xml_node;
    protected ilLogger $logger;
    protected DOMDocument $dom_doc;

    public function __construct(
        ilLogger $logger,
        ilXMLFileNodeInfoFactoryInterface $xml_node_factory,
    ) {
        $this->logger = $logger;
        $this->xml_node = $xml_node_factory;
    }

    /**
     * @throws ilImportStatusException
     */
    public function withFileHandler(ilXMLFileHandlerInterface $file_handler): ilParseHandlerInterface
    {
        $clone = clone $this;
        $clone->xml_file_handler = $file_handler;
        $clone->dom_doc = $file_handler->loadDomDocument();
        return $clone;
    }

    public function getNodeInfoAt(ilFilePathHandlerInterface $path): ilXMLFileNodeInfoCollectionInterface
    {
        $dom_xpath = new DOMXPath($this->dom_doc);
        foreach ($this->xml_file_handler->getNamespaces() as $namespace) {
            $dom_xpath->registerNamespace($namespace->getPrefix(), $namespace->getNamespace());
        }
        $nodes = $dom_xpath->query($path->toString());
        $node_info_collection = $this->xml_node->collection();
        /** @var DOMNode $node **/
        foreach ($nodes as $node) {
            $node_info = $this->xml_node->withDOMNode($node);
            $node_info_collection = $node_info_collection->withElement($node_info);
        }
        return $node_info_collection;
    }
}
