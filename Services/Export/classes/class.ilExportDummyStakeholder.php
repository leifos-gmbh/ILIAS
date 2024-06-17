<?php

namespace ILIAS\Export;

use ILIAS\ResourceStorage\Stakeholder\AbstractResourceStakeholder;

class ilExportDummyStakeholder extends AbstractResourceStakeholder
{
    public function __construct()
    {
    }

    public function getId(): string
    {
        return 'exp';
    }

    public function getOwnerOfNewResources(): int
    {
        return 6; // maybe id of export holding object?
    }
}
