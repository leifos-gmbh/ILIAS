<?php

namespace ImportHandler\I\Parser\Path\Node;

use ImportHandler\I\Parser\Path\Node\ilAnyElementInterface as ilParserPathAnyElementNodeInterface;
use ImportHandler\I\Parser\Path\Node\ilAnyNodeInterface as ilParserPathAnyNodeNodeInterface;
use ImportHandler\I\Parser\Path\Node\ilAttributableInterface as ilParserPathAttributableNodeInterface;
use ImportHandler\I\Parser\Path\Node\ilIndexableInterface as ilParserPathIndexableNodeInterface;
use ImportHandler\I\Parser\Path\Node\ilSimpleInterface as ilParserPathSimpleNodeInterface;

interface ilFactoryInterface
{
    public function anyElement(): ilParserPathAnyElementNodeInterface;

    public function anyNode(): ilParserPathAnyNodeNodeInterface;

    public function attributable(): ilParserPathAttributableNodeInterface;

    public function indexable(): ilParserPathIndexableNodeInterface;

    public function simple(): ilParserPathSimpleNodeInterface;
}
