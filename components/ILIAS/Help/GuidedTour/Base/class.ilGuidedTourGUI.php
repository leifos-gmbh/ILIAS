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
use ILIAS\Services\Help\ScreenId\HelpScreenIdObserver;

/**
 * @ilCtrl_Calls ilGuidedTourGUI: ilGuidedTourPageGUI
 */
class ilGuidedTourGUI implements ilCtrlBaseClassInterface
{
    protected \ILIAS\Help\GuidedTour\Settings\SettingsManager $settings_manager;
    protected \ILIAS\Help\GuidedTour\StandardGUIRequest $request;
    protected \ILIAS\Help\GuidedTour\Page\PageManager $page_manager;
    protected \ILIAS\Help\GuidedTour\Tour\TourManager $tour_manager;
    protected \ILIAS\Help\GuidedTour\Step\StepManager $step_manager;
    protected \ILIAS\Help\InternalGUIService $gui;
    protected \ILIAS\Help\InternalService $help;

    public function __construct()
    {
        global $DIC;
        $this->help = $DIC->help()->internal();
        $this->gui = $this->help->gui();
        $this->tour_manager = $this->help->domain()->guidedTour()->tour();
        $this->step_manager = $this->help->domain()->guidedTour()->step();
        $this->page_manager = $this->help->domain()->guidedTour()->page();
        $this->settings_manager = $this->help->domain()->guidedTour()->tourSettings();
        $this->request = $this->gui->guidedTour()->standardRequest();
    }

    public function executeCommand() : void
    {
        $ctrl = $this->gui->ctrl();

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd();

        switch ($next_class) {
            default:
                if (in_array($cmd, [
                    "getData",
                    "showStep"
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

    public function getData() : void
    {
        $f = $this->gui->ui()->factory();
        $r = $this->gui->ui()->renderer();
        $ctrl = $this->gui->ctrl();
        $popover = $f->popover()->standard($f->legacy(''));
        $data = new \stdClass();
        $data->popoverHtml = $r->renderAsync($popover);
        $data->popoverShowSignal = $popover->getShowSignal()->getId();
        foreach ($this->tour_manager->getAll() as $tour) {
            $settings = $this->settings_manager->getByObjId($tour->getId());
            if (!$settings?->isActive()) {
                continue;
            }
            $data->tour[$tour->getId()] = [
                "name" => $tour->getTitle()
            ];
            foreach ($this->step_manager->getStepsOfTour($tour->getId()) as $step) {
                $step_id = $step->getId();
                $ctrl->setParameterByClass(self::class, "tour_id", $tour->getId());
                $ctrl->setParameterByClass(self::class, "step_id", $step_id);
                $data->tour[$tour->getId()]["steps"][$step_id] = [
                    "id" => $step_id,
                    "type" => $step->getType(),
                    "elementId" => $step->getElementId(),
                    "url" => $ctrl->getLinkTargetByClass(ilGuidedTourPageGUI::class, "showStep"),
                ];
            }
        }
        $this->gui->httpUtil()->sendJson($data);
    }

    protected function showStep() : void
    {
        $tour_id = $this->request->getTourId();
        $step_id = $this->request->getStepId();
        $this->page_manager->printPage($step_id);
    }
}
