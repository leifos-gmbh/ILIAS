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
use ImportHandler\I\File\XML\Node\Info\ilHandler as ilXMLFileNodeInfoInterface;

class ilInfo implements ilXMLFileNodeInfoInterface
{
    /**
     * @var array<string, string>
     */
    protected array $attributes;
    protected DOMNode $node;

    public function __construct()
    {
        $this->attributes = [];
    }

    protected function checkIfNodeIsSet(): void
    {
        if (!isset($this->node)) {
            throw new ilImportException('DOMNode of NodeInfo not set.');
        }
    }

    protected function checkIfAttributeExists(string $attribute_name): void
    {
        if (!array_key_exists($attribute_name, $this->attributes)) {
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

    public function getNode(): DOMNode
    {
        return $this->node;
    }
}
