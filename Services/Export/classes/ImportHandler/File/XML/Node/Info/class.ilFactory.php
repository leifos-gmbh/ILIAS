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

use ImportHandler\I\File\XML\Node\Info\ilFactoryInterface as ilXMLFileNodeInfoFactoryInterface;
use ImportHandler\I\File\XML\Node\Info\ilHandlerCollection as ilXMLFileNodeInfoCollectionInterface;
use ImportHandler\I\File\XML\Node\Info\ilHandler as ilXMLFileNodeInfoHandlerInterface;
use ImportHandler\File\XML\Node\Info\ilInfo as ilXMLFileNodeInfo;
use ImportHandler\File\XML\Node\Info\ilInfoCollection as ilFileNodeInfoCollection;

class ilFactory implements ilXMLFileNodeInfoFactoryInterface
{
    public function handler(): ilXMLFileNodeInfoHandlerInterface
    {
        return new ilXMLFileNodeInfo();
    }

    public function handlerCollection(
        ilXMLFileNodeInfoHandlerInterface ...$initial_elements
    ): ilXMLFileNodeInfoCollectionInterface {
        return new ilFileNodeInfoCollection($initial_elements);
    }
}
