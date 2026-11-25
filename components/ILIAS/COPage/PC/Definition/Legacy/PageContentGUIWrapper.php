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

namespace ILIAS\COPage\PC\Definition\Legacy;

use ILIAS\COPage\PC\Definition\GUI\PageContentGUIInterface;
use ilPageContentGUI;

class PageContentGUIWrapper implements PageContentGUIInterface
{
    public function __construct(
        protected ilPageContentGUI $content_gui,
        protected bool $top_item
    ) {
    }

    public function executeCommand(): void
    {
        $this->content_gui->executeCommand();
    }

    public function hasCreateCommand(): bool
    {
        return $this->top_item;
    }

    public function getCreateCommand(): string
    {
        return 'insert';
    }

    public function hasEditCommand(): bool
    {
        return true;
    }

    public function getEditCommand(): string
    {
        return 'edit';
    }
}
