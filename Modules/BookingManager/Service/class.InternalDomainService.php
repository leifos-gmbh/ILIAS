<?php declare(strict_types = 1);

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

namespace ILIAS\BookingManager;

use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICDomainServices;
use ILIAS\BookingManager\BookingProcess\BookingProcessManager;
use ILIAS\BookingManager\Objects\ObjectsManager;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalDomainService
{
    use GlobalDICDomainServices;

    protected static array $object_manager = [];
    protected InternalRepoService $repo_service;
    protected InternalDataService $data_service;

    public function __construct(
        Container $DIC,
        InternalRepoService $repo_service,
        InternalDataService $data_service
    ) {
        $this->repo_service = $repo_service;
        $this->data_service = $data_service;
        $this->initDomainServices($DIC);
    }

    /*
    public function access(int $ref_id, int $user_id) : Access\AccessManager
    {
        return new Access\AccessManager(
            $this,
            $this->access,
            $ref_id,
            $user_id
        );
    }*/

    public function preferences(
        \ilObjBookingPool $pool
    ) : \ilBookingPreferencesManager {
        return new \ilBookingPreferencesManager(
            $pool,
            $this->repo_service->preferenceBasedBooking()
        );
    }

    public function process() : BookingProcessManager
    {
        $user_id = $this->user()->getId();
        $user_settings = \ilCalendarUserSettings::_getInstanceByUserId($user_id);
        return new BookingProcessManager(
        );
    }

    public function objects(int $pool_id) : ObjectsManager
    {
        if (!isset(self::$object_manager[$pool_id])) {
            self::$object_manager[$pool_id] = new ObjectsManager(
                $this->data_service,
                $this->repo_service,
                $this,
                $pool_id
            );
        }
        return self::$object_manager[$pool_id];
    }
}
