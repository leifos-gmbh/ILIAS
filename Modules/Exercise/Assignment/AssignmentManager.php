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

use ILIAS\Exercise\InternalRepoService;

class AssignmentManager
{
    public const TYPE_ALL = 0;
    public const TYPE_ONGOING = 1;
    public const TYPE_FUTURE = 2;
    public const TYPE_PAST = 3;
    protected AssignmentsDBRepository $repo;
    protected int $obj_id;
    protected int $ref_id;

    public function __construct(
        InternalRepoService $repo_service,
        int $ref_id
    ) {
        $this->ref_id = $ref_id;
        $this->obj_id = \ilObject::_lookupObjId($ref_id);
        $this->repo = $repo_service->assignment()->assignments();
    }

    protected function getExcRefId() : int
    {
        return $this->ref_id;
    }

    protected function getExcId() : int
    {
        return $this->obj_id;
    }

    /**
     * @return iterable<Assignment>
     */
    public function getList() : \Iterator
    {
        return $this->repo->getList($this->getExcId());
    }
}