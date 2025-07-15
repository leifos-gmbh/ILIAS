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
use ILIAS\Repository\Form\FormAdapterGUI;

class ilGuidedTourAdminGUI implements ilCtrlBaseClassInterface
{

    protected \ILIAS\Help\GuidedTour\Tour\TourManager $tm;

    public function __construct(
        protected \ILIAS\Help\GuidedTour\InternalDataService $data,
        protected \ILIAS\Help\GuidedTour\InternalDomainService $domain,
        protected \ILIAS\Help\GuidedTour\InternalGUIService $gui
    )
    {
        $this->tm = $domain->tour();
    }

    public function executeCommand() : void
    {
        $ctrl = $this->gui->ctrl();

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("show");

        switch ($next_class) {
            default:
                if (in_array($cmd, [
                    "show",
                    "addTour",
                    "saveTour",
                ])) {
                    $this->$cmd();
                }
        }
    }

    protected function show() : void
    {
        $mt = $this->gui->ui()->mainTemplate();
        $f = $this->gui->ui()->factory();
        $r = $this->gui->ui()->renderer();
        $ctrl = $this->gui->ctrl();
        $lng = $this->domain->lng();

        $b = $f->button()->standard(
            $lng->txt("add_tour"),
            $ctrl->getLinkTarget($this, "addTour")
        );
        $this->gui->toolbar()->addComponent($b);

        $items = [];
        foreach ($this->tm->getAll() as $tour) {
            $items[] = $f->item()->standard($tour->getTitle());
        }
        if (count($items) > 0) {
            $grp = $f->item()->group("", $items);
            $panel = $f->panel()->listing()->standard(
                $lng->txt("guided_tours"),
                [$grp]
            );
            $mt->setContent($r->render([$panel]));
        }
    }

    protected function getCreateForm() : FormAdapterGUI
    {
        return $this->gui->form(self::class, "saveTour")
            ->addStdTitleAndDescription(0, "gdtr");
    }

    protected function addTour() : void
    {
        $mt = $this->gui->ui()->mainTemplate();
        $mt->setContent($this->getCreateForm()->render());
    }

    public function saveTour() : void
    {
        $mt = $this->gui->ui()->mainTemplate();
        $form = $this->getCreateForm();
        if ($form->isValid()) {
            $obj_id = $this->tm->createTour("dummy", "");
            $form->saveStdTitleAndDescription($obj_id, "gdtr");
        } else {
            $mt->setContent($form->render());
        }
    }

}
