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

namespace ILIAS\MetaData\OERHarvester\RepositoryObjects;

interface HandlerInterface
{
    /**
     * Returns the new ref ID.
     */
    public function referenceObjectInTargetContainer(int $obj_id, int $container_ref_id): int;

    public function isObjectReferencedInContainer(int $obj_id, int $container_ref_id): bool;

    public function isReferenceDeleted(int $ref_id): bool;
}
