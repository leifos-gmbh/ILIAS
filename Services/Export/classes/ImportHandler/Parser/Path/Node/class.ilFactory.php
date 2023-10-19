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

namespace ImportHandler\Parser\Path\Node;

use ImportHandler\I\Parser\Path\Node\ilAnyElementInterface as ilParserPathAnyElementNodeInterface;
use ImportHandler\I\Parser\Path\Node\ilAnyNodeInterface as ilParserPathAnyNodeNodeInterface;
use ImportHandler\I\Parser\Path\Node\ilAttributableInterface as ilParserPathAttributableNodeInterface;
use ImportHandler\I\Parser\Path\Node\ilFactoryInterface as ilParserPathNodeFactory;
use ImportHandler\I\Parser\Path\Node\ilIndexableInterface as ilParserPathIndexableNodeInterface;
use ImportHandler\I\Parser\Path\Node\ilSimpleInterface as ilParserPathSimpleNodeInterface;
use ImportHandler\Parser\Path\Node\ilAnyElement as ilParserPathAnyElementNode;
use ImportHandler\Parser\Path\Node\ilAnyNode as ilParserPathAnyNodeNode;
use ImportHandler\Parser\Path\Node\ilAttributable as ilParserPathAttributableNode;
use ImportHandler\Parser\Path\Node\ilIndexable as ilParserPathIndexableNode;
use ImportHandler\Parser\Path\Node\ilSimple as ilParserPathSimpleNode;

class ilFactory implements ilParserPathNodeFactory
{
    public function anyElement(): ilParserPathAnyElementNodeInterface
    {
        return new ilParserPathAnyElementNode();
    }

    public function anyNode(): ilParserPathAnyNodeNodeInterface
    {
        return new ilParserPathAnyNodeNode();
    }

    public function attributable(): ilParserPathAttributableNodeInterface
    {
        return new ilParserPathAttributableNode();
    }

    public function indexable(): ilParserPathIndexableNodeInterface
    {
        return new ilParserPathIndexableNode();
    }

    public function simple(): ilParserPathSimpleNodeInterface
    {
        return new ilParserPathSimpleNode();
    }
}
