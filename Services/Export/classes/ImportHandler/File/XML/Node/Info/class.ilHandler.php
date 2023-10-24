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

namespace ImportHandler\File\XML\Node\Info;

use DOMAttr;
use DOMNode;
use ilImportException;
use ImportHandler\I\File\XML\Node\Info\ilHandlerInterface as ilXMLFileNodeInfoInterface;
use ImportHandler\I\File\XML\Node\Info\ilCollectionInterface as ilXMLFileNodeInfoCollectionInterface;
use ImportHandler\I\File\XML\Node\Info\ilFactoryInterface as ilXMLFileNodeInfoFactoryInterface;

class ilHandler implements ilXMLFileNodeInfoInterface
{
    /**
     * @var array<string, string>
     */
    protected array $attributes;
    protected DOMNode $node;
    protected ilXMLFileNodeInfoFactoryInterface $info;

    public function __construct(
        ilXMLFileNodeInfoFactoryInterface $info
    ) {
        $this->attributes = [];
        $this->info = $info;
    }

    protected function checkIfNodeIsSet(): void
    {
        if (!isset($this->node)) {
            throw new ilImportException('DOMNode of NodeInfo not set.');
        }
    }

    protected function checkIfAttributeExists(string $attribute_name): void
    {
        if (!$this->hasAttribute($attribute_name)) {
            throw new ilImportException('Node info does not contain attribute with name: ' . $attribute_name);
        }
    }

    protected function initAttributes()
    {
        $this->checkIfNodeIsSet();
        /** @var DOMAttr $attribute **/
        foreach ($this->node->attributes as $attribute) {
            $this->attributes[$attribute->name] = $attribute->value;
        }
    }

    public function withDOMNode(DOMNode $node): ilXMLFileNodeInfoInterface
    {
        $clone = clone $this;
        $clone->node = $node;
        $clone->initAttributes();
        return $clone;
    }

    public function getXML(): string
    {
        $this->checkIfNodeIsSet();
        return $this->node->ownerDocument->saveXML($this->node);
    }

    public function getNodeName(): string
    {
        $this->checkIfNodeIsSet();
        return $this->node->nodeName;
    }

    /**
     * @throws ilImportException when the attribute with $attribute_name does not exist.
     */
    public function getValueOfAttribute(string $attribute_name): string
    {
        $this->checkIfNodeIsSet();
        $this->checkIfAttributeExists($attribute_name);
        return $this->attributes[$attribute_name];
    }

    public function getAttributePath(string $attribute_name, string $path_separator): string
    {
        $path_str = $this->getValueOfAttribute($attribute_name);
        $current_node = $this;
        while (!is_null($current_node = $current_node->getParent())) {
            $path_str = $current_node->getValueOfAttribute($attribute_name) . $path_separator . $path_str;
        }
        return $path_str;
    }

    public function getChildren(): ilXMLFileNodeInfoCollectionInterface
    {
        $collection = $this->info->collection();
        $child = $this->node->firstChild;
        while (!is_null($child)) {
            $collection = $collection->withElement($this->info->handler()->withDOMNode($child));
            $child = $child->nextSibling;
        }
        return $collection;
    }

    public function getParent(): ilXMLFileNodeInfoInterface|null
    {
        if(!is_null($this->node->parentNode)) {
            return $this->info->handler()->withDOMNode($this->node->parentNode);
        }
        return null;
    }

    public function hasAttribute(string $attribute_name): bool
    {
        return array_key_exists($attribute_name, $this->attributes);
    }
}
