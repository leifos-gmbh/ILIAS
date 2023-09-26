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

namespace ILIAS\Help;

use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICDomainServices;
use ILIAS\Help\Map\MapManager;
use ILIAS\Help\Tooltips\TooltipsManager;
use ILIAS\Help\Module\ModuleManager;

class InternalDomainService
{
    use GlobalDICDomainServices;

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

    public function map(): MapManager
    {
        return new MapManager(
            $this->repo_service,
            $this
        );
    }

    public function tooltips(): TooltipsManager
    {
        return new TooltipsManager(
            $this->repo_service,
            $this
        );
    }

    public function module(): ModuleManager
    {
        return new ModuleManager(
            $this
        );
    }

}
