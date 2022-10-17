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
 * @ilCtrl_Calls ilParentBGUI: ilAGUI, ilBGUI, ilCGUI
 */
class ilParentBGUI
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

            case strtolower(ilAGUI::class):
                $ctrl->forwardCommand(new ilAGUI());
                break;

            case strtolower(ilBGUI::class):
                $ctrl->forwardCommand(new ilBGUI());
                break;

            case strtolower(ilCGUI::class):
                $html = $ctrl->getHTML(new ilAGUI());
                $html.= $ctrl->getHTML(new ilBGUI());
                $ctrl->forwardCommand(new ilCGUI());
                break;

            default:
                if (in_array($cmd, ["show"])) {
                    $this->$cmd();
                }
        }
        $this->main_tpl->printToStdout();
    }

    protected function show(): void
    {
        $ctrl = $this->ctrl;

        $html = "";

        // get html parts from ilAGUI and ilBGUI
        $html.= $ctrl->getHTML(new ilAGUI());
        $html.= $ctrl->getHTML(new ilBGUI());

        $link_to_c = $this->ui->factory()->link()->standard(
            "Link",
            $ctrl->getLinkTargetByClass(ilCGUI::class, "")
        );
        $html.= $this->ui->renderer()->render($link_to_c);

        $this->main_tpl->setContent($html);
    }
}
