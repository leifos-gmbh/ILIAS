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

namespace ILIAS\Help\GuidedTour\Tour;

use ILIAS\Help\GuidedTour\InternalDataService;
use ILIAS\Help\GuidedTour\InternalDomainService;
use ilObjGuidedTour;
use ILIAS\Help\GuidedTour\Settings\PermissionType;
use ilObjectFactory;

class TourManager
{
    private \ILIAS\Help\GuidedTour\Settings\SettingsManager $sm;

    public function __construct(
        protected InternalDataService $data,
        protected InternalDomainService $domain
    ) {
        $this->sm = $domain->tourSettings();
    }

    public function createTour(
        string $title,
        string $description
    ) : int
    {
        $tour_obj = new ilObjGuidedTour();
        $tour_obj->setTitle($title);
        $tour_obj->setDescription($description);
        $tour_obj->create();
        $this->sm->save($this->data->settings(
            $tour_obj->getId(),
            false,
            "",
            PermissionType::None
        ));
        return $tour_obj->getId();
    }

    /**
     * @return \Generator<ilObjGuidedTour>
     */
    public function getAll() : \Generator
    {
        foreach(\ilObject::_getObjectsByType("gdtr") as $tour) {
            yield \ilObjectFactory::getInstanceByObjId($tour["obj_id"]);
        }
    }

    public function getByObjId(int $obj_id) : ?ilObjGuidedTour
    {
        return \ilObjectFactory::getInstanceByObjId($obj_id);
    }

}