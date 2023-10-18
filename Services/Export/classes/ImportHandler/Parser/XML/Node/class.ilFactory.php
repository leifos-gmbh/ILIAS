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

namespace ImportHandler\Parser\XML\Node;

use ImportHandler\I\Parser\XML\Node\ilFactoryInterface as ilParserXMLNodeFactoryInterface;
use ImportHandler\I\Parser\XML\Node\ilInfoCollectionInterface as ilParserXMLNodeInfoCollectionInterface;
use ImportHandler\I\Parser\XML\Node\ilInfoInterface as ilParserXMLNodeInfoInterface;
use ImportHandler\Parser\XML\Node\ilInfoCollection as ilParserXMLNodeInfoCollection;
use ImportHandler\Parser\XML\Node\ilInfo as ilParserXMLNodeInfo;

class ilFactory implements ilParserXMLNodeFactoryInterface
{
    public function info(): ilParserXMLNodeInfoInterface
    {
        return new ilParserXMLNodeInfo();
    }

    public function infoCollection(
        ilParserXMLNodeInfoInterface ...$initial_elements
    ): ilParserXMLNodeInfoCollectionInterface {
        return new ilParserXMLNodeInfoCollection($initial_elements);
    }
}
