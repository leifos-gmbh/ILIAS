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


/**
 * Repository internal repo service
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalRepoService
{
    /**
     * @var InternalDataService
     */
    protected $data;

    /**
     * @var \ilDBInterface
     */
    protected $db;

    public function __construct(InternalDataService $data, \ilDBInterface $db)
    {
        $this->data = $data;
        $this->db = $db;
    }

    public function repositoryContainer() : Container\ContainerDBRepository
    {
        return new Container\ContainerDBRepository(
            $this->db
        );
    }

    /**
     * Objects without ref id (e.g. portfolios) can use
     * the manager with a ref_id of 0, e.g. to get selectable styles
     */
    public function object() : Object\ObjectDBRepository
    {
        return new Object\ObjectDBRepository(
            $this->db
        );
    }

}
