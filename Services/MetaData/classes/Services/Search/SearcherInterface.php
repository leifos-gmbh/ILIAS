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

namespace ILIAS\MetaData\Services\Search;

use ILIAS\MetaData\Elements\RessourceID\RessourceIDInterface;
use ILIAS\Metadata\Repository\Search\Clauses\FactoryInterface;
use ILIAS\Metadata\Repository\Search\Clauses\ClauseInterface;
use ILIAS\MetaData\Repository\Search\Filters\FilterInterface;

interface SearcherInterface
{
    public function getClauseFactory(): FactoryInterface;

    public function getFilter(
        ?int $obj_id,
        ?int $sub_id,
        ?string $type
    ): FilterInterface;

    /**
     * @return RessourceIDInterface[]
     */
    public function search(
        ClauseInterface $clause,
        FilterInterface ...$filters
    ): \Generator;
}
