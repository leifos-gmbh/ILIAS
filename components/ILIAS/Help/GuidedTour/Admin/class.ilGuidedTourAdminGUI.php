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

use ILIAS\Repository\Form\FormAdapterGUI;
use ILIAS\Help\GuidedTour\Step\StepType;
use ILIAS\Help\GuidedTour\Step\Step;

/**
 * @ilCtrl_Calls ilGuidedTourAdminGUI: ilGuidedTourPageGUI
 */
class ilGuidedTourAdminGUI implements ilCtrlBaseClassInterface
{

    private \ILIAS\Help\GuidedTour\Step\StepManager $step_manager;
    protected \ILIAS\Help\GuidedTour\Tour\TourManager $tm;

    public function __construct(
        protected \ILIAS\Help\GuidedTour\InternalDataService $data,
        protected \ILIAS\Help\GuidedTour\InternalDomainService $domain,
        protected \ILIAS\Help\GuidedTour\InternalGUIService $gui
    )
    {
        $ctrl = $this->gui->ctrl();
        $this->tm = $domain->tour();
        $ctrl->saveParameterByClass(self::class, "tour_id");
        $this->step_manager = $domain->step();
    }

    public function executeCommand() : void
    {
        $ctrl = $this->gui->ctrl();
        $mt = $this->gui->ui()->mainTemplate();

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("show");

        switch ($next_class) {
            case strtolower(ilGuidedTourPageGUI::class):
                $ctrl->setReturnByClass(self::class, "listSteps");
                $ctrl->saveParameterByClass(self::class, "step_id");
                $ret = $this->forwardToPageObject();
                $mt->setContent($ret);
                break;

            default:
                if (in_array($cmd, [
                    "show",
                    "addTour",
                    "saveTour",
                    "listSteps",
                    "addStep",
                    "saveStep",
                    "tableCommand",
                    "editStep",
                    "editPage",
                ])) {
                    $this->$cmd();
                }
        }
    }

    public function forwardToPageObject(): string
    {
        $tabs = $this->gui->tabs();
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();
        $step_id = $this->gui->standardRequest()->getStepId();
        $tour_id = $this->gui->standardRequest()->getTourId();

        $tabs->clearTargets();
        $tabs->setBackTarget(
            $lng->txt("back"),
            $ctrl->getLinkTargetByClass(ilGuidedTourPageGUI::class, "edit")
        );

        if (!ilGuidedTourPage::_exists(
            "gdtr",
            $step_id
        )) {
            $new_page_object = new ilGuidedTourPage();
            $new_page_object->setParentId($tour_id);
            $new_page_object->setId($step_id);
            $new_page_object->createFromXML();
        }

        // get page object
        $page_gui = new ilGuidedTourPageGUI($step_id);
        /*$page_gui->setStyleId(
            $style->getEffectiveStyleId()
        );*/
        $page_gui->setTemplateTargetVar("ADM_CONTENT");
        $page_gui->setFileDownloadLink("");
        $page_gui->setPresentationTitle("");
        $page_gui->setTemplateOutput(false);

        // style tab
        //$page_gui->setTabHook($this, "addPageTabs");

        return $ctrl->forwardCommand($page_gui);
    }


    protected function show() : void
    {
        $mt = $this->gui->ui()->mainTemplate();
        $f = $this->gui->ui()->factory();
        $r = $this->gui->ui()->renderer();
        $ctrl = $this->gui->ctrl();
        $lng = $this->domain->lng();

        $b = $f->button()->standard(
            $lng->txt("gdtr_add_tour"),
            $ctrl->getLinkTarget($this, "addTour")
        );
        $this->gui->toolbar()->addComponent($b);

        $items = [];
        foreach ($this->tm->getAll() as $tour) {
            $ctrl->setParameterByClass(self::class, "tour_id", $tour->getId());
            $link = $f->link()->standard(
               $lng->txt("gdtr_edit_steps"),
               $ctrl->getLinkTargetByClass(self::class, "listSteps")
            );
            $items[] = $f->item()->standard($tour->getTitle())
                ->withActions($f->dropdown()->standard([$link]));
        }
        if (count($items) > 0) {
            $grp = $f->item()->group("", $items);
            $panel = $f->panel()->listing()->standard(
                $lng->txt("gdtr_guided_tours"),
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

    protected function setStepsHeader() : void
    {
        $tabs = $this->gui->tabs();
        $lng = $this->domain->lng();
        $mt = $this->gui->ui()->mainTemplate();
        $ctrl = $this->gui->ctrl();
        $tour = $this->tm->getByObjId($this->gui->standardRequest()->getTourId());
        $mt->setTitle($lng->txt("guided_tour") . ": " . $tour?->getTitle());
        $mt->setDescription($tour?->getDescription());
        $tabs->clearTargets();
        $tabs->setBackTarget(
            $lng->txt("back"),
            $ctrl->getLinkTargetByClass(self::class, "show")
        );
    }

    protected function setSingleStepHeader() : void
    {
        $this->setStepsHeader();
        $tabs = $this->gui->tabs();
        $lng = $this->domain->lng();
        $ctrl = $this->gui->ctrl();
        $tabs->setBackTarget(
            $lng->txt("back"),
            $ctrl->getLinkTargetByClass(self::class, "listSteps")
        );
    }

    protected function listSteps() : void
    {
        $mt = $this->gui->ui()->mainTemplate();
        $f = $this->gui->ui()->factory();
        $ctrl = $this->gui->ctrl();
        $lng = $this->domain->lng();
        $this->setStepsHeader();

        $b = $f->button()->standard(
            $lng->txt("gdtr_add_step"),
            $ctrl->getLinkTarget($this, "addStep")
        );
        $this->gui->toolbar()->addComponent($b);

        $table = $this->gui->stepTableGUI(
            $this->gui->standardRequest()->getTourId(),
            $this
        );
        $mt->setContent($table->render());
    }

    public function tableCommand(): void
    {
        $table = $this->gui->stepTableGUI(
            $this->gui->standardRequest()->getTourId(),
            $this
        );
        $table->handleCommand();
    }

    protected function addStep() : void
    {
        $this->setSingleStepHeader();
        $mt = $this->gui->ui()->mainTemplate();
        $mt->setContent($this->getStepForm()->render());
    }

    protected function getStepForm(?Step $step = null) : FormAdapterGUI
    {
        $lng = $this->domain->lng();
        $type_val = (string) $step?->getType()->value;
        $mb_element_id = $step?->getType()->value === StepType::Mainbar->value
            ? $step?->getElementId()
            : null;
        $mt_element_id = $step?->getType()->value === StepType::Metabar->value
            ? $step?->getElementId()
            : null;
        $tab_element_id = $step?->getType()->value === StepType::Tab->value
            ? $step?->getElementId()
            : null;
        return $this->gui->form(self::class, "saveStep")
            ->switch("type", $lng->txt("gdtr_step_type"), "", $type_val)
            ->group((string) StepType::Mainbar->value, $lng->txt("gdtr_mainbar"))
            ->text("mb_element_id", $lng->txt("gdtr_element_id"), "", $mb_element_id)
            ->group((string) StepType::Metabar->value, $lng->txt("gdtr_metabar"))
            ->text("mt_element_id", $lng->txt("gdtr_element_id"), "", $mt_element_id)
            ->group((string) StepType::Tab->value, $lng->txt("gdtr_tabs"))
            ->text("tab_element_id", $lng->txt("gdtr_element_id"), "", $tab_element_id)
            ->group((string) StepType::Form->value, $lng->txt("gdtr_form"))
            ->group((string) StepType::Table->value, $lng->txt("gdtr_table"))
            ->group((string) StepType::Toolbar->value, $lng->txt("gdtr_toolbar"))
            ->group((string) StepType::PrimaryButton->value, $lng->txt("gdtr_primary_button"))
            ->end();
    }

    public function saveStep() : void
    {
        $ctrl = $this->gui->ctrl();
        $mt = $this->gui->ui()->mainTemplate();
        $oder_nr = 0;
        if (($step_id = $this->gui->standardRequest()->getStepId()) > 0) {
            $step = $this->step_manager->getById($step_id);
            $oder_nr = $step->getOrderNr();
        }
        $form = $this->getStepForm();
        if ($form->isValid()) {
            $element_id = match ((int) $form->getData("type")) {
                StepType::Mainbar->value => $form->getData("mb_element_id"),
                StepType::Metabar->value => $form->getData("mt_element_id"),
                StepType::Tab->value => $form->getData("tab_element_id"),
                default => ''
            };
            $step = $this->data->step(
                $step_id,
                $this->gui->standardRequest()->getTourId(),
                $oder_nr,
                StepType::from((int) $form->getData("type")),
                $element_id
            );
            if ($step_id > 0) {
                $this->step_manager->update($step);
            } else {
                $this->step_manager->create($step);
            }
            $ctrl->redirectByClass(self::class, "listSteps");
        } else {
            $mt->setContent($form->render());
        }
    }

    public function editStep(int $step_id) : void
    {
        $this->setSingleStepHeader();
        $ctrl = $this->gui->ctrl();
        $ctrl->setParameterByClass(self::class, "step_id", $step_id);
        $step = $this->step_manager->getById($step_id);
        $form = $this->getStepForm($step);
        $mt = $this->gui->ui()->mainTemplate();
        $mt->setContent($form->render());
    }

    public function editPage(int $step_id) : void
    {
        $ctrl = $this->gui->ctrl();
        $ctrl->setParameterByClass(self::class, "step_id", $step_id);
        $ctrl->redirectByClass(ilGuidedTourPageGUI::class, "edit");
    }

}
