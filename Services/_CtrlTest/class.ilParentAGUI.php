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
 * @ilCtrl_Calls ilParentAGUI: ilParentBGUI
 */
class ilParentAGUI implements ilCtrlBaseClassInterface
{
    protected ilCtrl $ctrl;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
    }

    public function executeCommand(): void
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("show");

        switch ($next_class) {

            case strtolower(ilParentBGUI::class):
                $ctrl->forwardCommand(new ilParentBGUI());
                break;

            default:
                $ctrl->redirectByClass(ilParentBGUI::class);
                break;
        }
    }
}
