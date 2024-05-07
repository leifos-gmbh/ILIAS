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
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\Elements\NullSet;

class SourceSelector implements SourceSelectorInterface
{
    protected RepositoryInterface $repository;

    public function __construct(
        RepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function fromObject(int $obj_id, int $sub_id, string $type): DerivatorInterface
    {
        if ($sub_id === 0) {
            $sub_id = $obj_id;
        }

        return $this->getDerivator(
            $this->repository->getMD($obj_id, $sub_id, $type)
        );
    }

    public function fromXML(\SimpleXMLElement $xml): DerivatorInterface
    {
        /**
         * TODO implement this
         */
        return $this->getDerivator(
            new NullSet()
        );
    }

    protected function getDerivator(SetInterface $from_set): DerivatorInterface
    {
        return new Derivator(
            $from_set,
            $this->repository
        );
    }
}
