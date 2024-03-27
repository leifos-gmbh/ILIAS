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

namespace ILIAS\MetaData\Services\Derivation;

use ILIAS\MetaData\Repository\RepositoryInterface;

class FromObjectDerivator implements FromObjectDerivatorInterface
{
    protected int $from_obj_id;
    protected int $from_sub_id;
    protected string $from_type;

    protected RepositoryInterface $repository;

    public function __construct(
        int $from_obj_id,
        int $from_sub_id,
        string $from_type,
        RepositoryInterface $repository
    ) {
        $this->from_obj_id = $from_obj_id;
        $this->from_sub_id = $from_sub_id;
        $this->from_type = $from_type;
        $this->repository = $repository;
    }

    public function forObject(int $obj_id, int $sub_id, string $type): void
    {
        if ($sub_id === 0) {
            $sub_id = $obj_id;
        }

        $this->repository->copyMD(
            $this->from_obj_id,
            $this->from_sub_id,
            $this->from_type,
            $obj_id,
            $sub_id,
            $type
        );
    }

    public function forXML(): \SimpleXMLElement
    {
        /**
         * TODO: implement this
         */
        return new \SimpleXMLElement('<xml></xml>');
    }
}
