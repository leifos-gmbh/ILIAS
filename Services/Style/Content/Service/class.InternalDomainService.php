<?php declare(strict_types = 1);

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
 */

namespace ILIAS\Style\Content;

use ILIAS\DI\Container;
use ILIAS\Repository\GlobalDICDomainServices;
use ILIAS\Style\Content\Container\ContainerManager;
use ILIAS\Style\Content\Object\ObjectManager;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalDomainService
{
    use GlobalDICDomainServices;

    /**
     * @var InternalRepoService
     */
    protected $repo_service;
    /**
     * @var InternalDataService
     */
    protected $data_service;

    public function __construct(
        Container $DIC,
        InternalRepoService $repo_service,
        InternalDataService $data_service
    ) {
        $this->repo_service = $repo_service;
        $this->data_service = $data_service;
        $this->initDomainServices($DIC);
    }

    public function repositoryContainer(int $ref_id) : ContainerManager
    {
        return new ContainerManager(
            $this->repo_service,
            $ref_id
        );
    }

    /**
     * Objects without ref id (e.g. portfolios) can use
     * the manager with a ref_id of 0, e.g. to get selectable styles
     */
    public function object(int $ref_id, int $obj_id = 0) : ObjectManager
    {
        return new ObjectManager(
            $this->repo_service,
            $this,
            $ref_id,
            $obj_id
        );
    }
}
