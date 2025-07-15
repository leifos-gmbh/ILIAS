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

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item;
use ILIAS\Help\StandardGUIRequest;
use ILIAS\Services\Help\ScreenId\HelpScreenIdObserver;

class ilGuidedTourGUI implements ilCtrlBaseClassInterface
{
    protected \ILIAS\Help\InternalGUIService $gui;
    protected \ILIAS\Help\InternalService $help;

    public function __construct()
    {
        global $DIC;
        $this->help = $DIC->help()->internal();
        $this->gui = $this->help->gui();
    }

    public function executeCommand() : void
    {
        $ctrl = $this->gui->ctrl();

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd();

        switch ($next_class) {
            default:
                if (in_array($cmd, [
                    "getPopover"
                ])) {
                    $this->$cmd();
                }
        }
    }

    public function init() : void
    {
        $mt = $this->gui->ui()->mainTemplate();
        $f = $this->gui->ui()->factory();
        $r = $this->gui->ui()->renderer();
        $ctrl = $this->gui->ctrl();

        // ensure popover js being loaded
        $r->render($f->popover()->standard($f->legacy('')));

        $debug = true;
        if ($debug) {
            $mt->addJavaScript("../components/ILIAS/Help/resources/guided-tour.js");
        } else {
            $mt->addJavaScript("assets/js/guided-tour.js");
        }
        $target = $ctrl->getLinkTargetByClass(self::class, "", "", true);
        $mt->addOnloadCode("il.guidedTour.init('$target');");
    }

    public function getPopover() : void
    {
        $f = $this->gui->ui()->factory();
        $r = $this->gui->ui()->renderer();
        $popover = $f->popover()->standard($f->legacy('Hello World'));
        $data = new \stdClass();
        $data->html = $r->renderAsync($popover);
        $data->showSignal = $popover->getShowSignal()->getId();
        $this->gui->httpUtil()->sendJson($data);
    }
}
