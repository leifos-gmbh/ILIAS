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

declare(strict_types=0);

namespace ILIAS\Tracking\View;

use ilDBInterface;
use ILIAS\Tracking\View\DataRetrieval\DataRetrievalInterface;
use ILIAS\Tracking\View\DataRetrieval\DataRetrieval;
use ILIAS\Tracking\View\PropertyList\FactoryInterface;
use ILIAS\Tracking\View\PropertyList\Factory;
use ILIAS\Tracking\View\Renderer\RendererInterface;
use ILIAS\Tracking\View\Renderer\Renderer;
use ILIAS\Tracking\View\ViewInterface;
use ILIAS\DI\UIServices;

class ViewInfo implements ViewInterface
{
    protected UIServices $ui;
    protected ilDBInterface $db;

    public function __construct()
    {
        global $DIC;
        $this->ui = $DIC->ui();
        $this->db = $DIC->database();
    }

    public function dataRetrieval(): DataRetrievalInterface
    {
        return new DataRetrieval(
            $this->db
        );
    }

    public function propertyList(): FactoryInterface
    {
        return new Factory();
    }

    public function renderer(): RendererInterface
    {
        return new Renderer(
            $this->ui
        );
    }
}
