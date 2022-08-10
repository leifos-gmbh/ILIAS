<?php declare(strict_types=1);

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
 *********************************************************************/

namespace ILIAS\BookingManager\Objects;

use ILIAS\BookingManager\InternalRepoService;
use ILIAS\BookingManager\InternalDataService;
use ILIAS\BookingManager\InternalDomainService;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ObjectsManager
{
    protected ObjectsDBRepository $object_repo;
    protected InternalRepoService $repo;
    protected InternalDataService $data;
    protected InternalDomainService $domain;

    public function __construct(
        InternalDataService $data,
        InternalRepoService $repo,
        InternalDomainService $domain,
        int $pool_id
    ) {
        $this->object_repo = $repo->objects();
        $this->object_repo->loadDataOfPool($pool_id);
    }

    public function getNrOfItemsForObject(int $book_obj_id) : int
    {
        return $this->object_repo->getNrOfItemsForObject($book_obj_id);
    }
}
