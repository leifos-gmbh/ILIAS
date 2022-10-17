<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilCGUI
{
    protected \ILIAS\DI\UIServices $ui;
    protected ilGlobalTemplateInterface $main_tpl;
    protected ilCtrl $ctrl;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->ui = $DIC->ui();
    }

    public function executeCommand(): void
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("show");

        switch ($next_class) {
            default:
                if (in_array($cmd, ["show"])) {
                    $this->$cmd();
                }
        }
    }

    protected function show(): void
    {
        $ctrl = $this->ctrl;

        $html = "";

        $link_to_self = $this->ui->factory()->link()->standard(
            "Link",
            $ctrl->getLinkTargetByClass(ilCGUI::class, "")
        );
        $html.= $this->ui->renderer()->render($link_to_self);

        $this->main_tpl->setContent($html);
    }
}
