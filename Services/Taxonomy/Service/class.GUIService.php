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

namespace ILIAS\Taxonomy;

class GUIService
{
    protected InternalGUIService $internal_gui_service;

    public function __construct(InternalGUIService $internal_gui_service)
    {
        $this->internal_gui_service = $internal_gui_service;
    }

    public function addSubTab(int $rep_obj_id) : void
    {
        $this->internal_gui_service->addSubTab($rep_obj_id);
    }

    public function getObjTaxonomyGUI(int $rep_obj_id) : \ilObjTaxonomyGUI
    {
        return $this->internal_gui_service->getObjTaxonomyGUI($rep_obj_id);
    }

}
