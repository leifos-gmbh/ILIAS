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

namespace ILIAS\Exercise\Assignment;

use ILIAS\Exercise\InternalDomainService;
use ILIAS\Exercise\InternalGUIService;
use ILIAS\Exercise\IRSS\CollectionWrapperGUI;

class GUIService
{
    protected CollectionWrapperGUI $irss_wrapper_gui;
    protected InternalDomainService $domain;
    protected InternalGUIService $gui;


    public function __construct(
        InternalDomainService $domain,
        InternalGUIService $gui
    ) {
        $this->domain = $domain;
        $this->gui = $gui;
        $this->irss_wrapper_gui = new CollectionWrapperGUI();
    }

    public function getRandomAssignmentGUI(\ilObjExercise $exc = null): \ilExcRandomAssignmentGUI
    {
        if ($exc === null) {
            $exc = $this->gui->request()->getExercise();
        }
        return new \ilExcRandomAssignmentGUI(
            $this->ui(),
            $this->toolbar(),
            $this->lng,
            $this->ctrl(),
            $this->service->domain()->assignment()->randomAssignments($exc)
        );
    }

    public function getInstructionFileResourceCollectionGUI(
        int $ref_id,
        int $ass_id
    ) : \ilResourceCollectionGUI
    {
        $irss = $this->domain->assignment()->instructionFiles($ass_id);
        $lng = $this->domain->lng();
        $lng->loadLanguageModule("exc");

        $write = $this->domain->access()->checkAccess('write', '', $ref_id);

        return $this->irss_wrapper_gui->getResourceCollectionGUI(
            $irss->getStakeholder(),
            $irss->getCollectionIdString(),
            $lng->txt('exc_instruction_files'),
            $write
        );
    }
}
