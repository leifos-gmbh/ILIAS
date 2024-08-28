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

namespace ILIAS\Export\ExportHandler\Repository;

use ILIAS\Export\ExportHandler\I\Repository\ilResourceStakeholderInterface as ilExportHandlerRepositoryResourceStakeholderInterface;
use ILIAS\ResourceStorage\Stakeholder\AbstractResourceStakeholder;

class ilResourceStakeholder extends AbstractResourceStakeholder implements ilExportHandlerRepositoryResourceStakeholderInterface
{
    protected int $owner_id;

    public function __construct(int $usr_id = 6)
    {
        $this->owner_id = $usr_id;
    }

    public function getId(): string
    {
        return "export_handler";
    }

    public function getOwnerOfNewResources(): int
    {
        return $this->owner_id;
    }

    public function withOwnerId(int $ownerId): ilExportHandlerRepositoryResourceStakeholderInterface
    {
        $clone = clone $this;
        $clone->owner_id = $ownerId;
        return $clone;
    }

    public function getOwnerId(): int
    {
        return $this->owner_id;
    }

    public function asAbstractResourceStakeholder(): AbstractResourceStakeholder
    {
        return $this;
    }
}
