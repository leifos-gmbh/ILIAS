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

namespace ILIAS\MetaData\Repository\Search\Clauses;

use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Repository\Search\Mode;
use ILIAS\MetaData\Repository\Search\Operator;

interface FactoryInterface
{
    /**
     * Basic search clause with the following semantics:
     * "Find all LOM sets that have at least one element at the
     * end of the path whose value fulfills the condition."
     */
    public function getBasicClause(
        PathInterface $path,
        Mode $mode,
        string $value
    ): ClauseInterface;

    /**
     * Note that joining clauses with the same paths can lead
     * to results that are at first glance counter-intuitive:
     * joining "Find all LOM sets with a keyword 'key'" and
     * "Find all LOM sets with a keyword 'different key'" with AND operator
     * can return results, since a LOM set can contain multiple
     * keywords.
     */
    public function getJoinedClauses(
        Operator $operator,
        ClauseInterface $first_clause,
        ClauseInterface $second_clause,
        ClauseInterface ...$further_clauses
    ): ClauseInterface;

    /**
     * Note that negation will only apply to the condition placed
     * on values of LOM elements: negating "Find all LOM sets with a keyword 'key'" gives
     * "Find all LOM sets with a keyword that is not 'key'", and **not**
     * "Find all LOM sets with **no** keyword 'key'".
     *
     * Further, negation of joined clauses will also apply to the operators
     * accordingly (negating AND-joined clauses will result in negated OR-joined clauses).
     */
    public function getNegatedClause(ClauseInterface $clause): ClauseInterface;
}
