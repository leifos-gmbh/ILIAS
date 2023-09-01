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

use ILIAS\Exercise\InternalDomainService;
use ILIAS\Exercise\InternalGUIService;

/**
 * @ilCtrl_Calls ilAssignmentPresentationGUI: ilAssignmentPresentationGUI
 */
class ilAssignmentPresentationGUI
{
    protected \ILIAS\Exercise\Assignment\PanelBuilderUI $panel_builder;
    protected ilObjUser $user;
    protected ilObjExercise $exc;
    protected InternalDomainService $domain_service;
    protected InternalGUIService $gui_service;

    public function __construct(
        ilObjExercise $exc,
        InternalDomainService $domain_service,
        InternalGUIService $gui_service,
    ) {
        $this->domain_service = $domain_service;
        $this->gui_service = $gui_service;
        $this->ctrl = $gui_service->ctrl();
        $this->main_tpl = $gui_service->ui()->mainTemplate();
        $this->exc = $exc;
        $this->user = $domain_service->user();
        $this->ass_manager = $domain_service->assignment()->assignments($exc->getRefId());
        $this->panel_builder = $gui_service->assignment()->panelBuilder(
            $domain_service->assignment()->mandatoryAssignments($this->exc)
        );
        $this->ass_id = $gui_service->request()->getAssId();
        $this->ctrl->saveParameter($this, "ass_id");
        $this->ui = $gui_service->ui();
    }

    function executeCommand() : void
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("showAssignment");

        switch ($next_class) {
            default:
                if (in_array($cmd, ["showAssignment"])) {
                    $this->$cmd();
                }
        }
    }

    public function showAssignment() : void
    {
        $r = $this->ui->renderer();
        $ass = $this->ass_manager->get($this->ass_id);
        $panel = $this->panel_builder->getPanel($ass, $this->user->getId());
        $this->main_tpl->setContent($r->render($panel));
    }

}