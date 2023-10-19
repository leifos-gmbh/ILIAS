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
