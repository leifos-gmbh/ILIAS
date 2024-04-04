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

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Repository\Search\Clauses\NullFactory as NullClauseFactory;
use ILIAS\MetaData\Repository\Search\Filters\NullFactory as NullFilterFactory;
use ILIAS\MetaData\Repository\Search\Filters\FilterInterface;
use ILIAS\MetaData\Repository\Search\Filters\NullFilter;
use ILIAS\MetaData\Repository\NullRepository;
use ILIAS\MetaData\Repository\Search\Clauses\ClauseInterface;
use ILIAS\MetaData\Repository\Search\Clauses\NullClause;

class SearcherTest extends TestCase
{
    public function getSearcher(): SearcherInterface
    {
        $clause_factory = new NullClauseFactory();
        $filter_factory = new class () extends NullFilterFactory {
            public function get(?int $obj_id, ?int $sub_id, ?string $type): FilterInterface
            {
                return new class ($obj_id, $sub_id, $type) extends NullFilter {
                    public array $data = [];

                    public function __construct(?int $obj_id, ?int $sub_id, ?string $type)
                    {
                        $this->data = [
                            'obj_id' => $obj_id,
                            'sub_id' => $sub_id,
                            'type' => $type
                        ];
                    }
                };
            }
        };
        $repository = new class () extends NullRepository {
            public function searchMD(
                ClauseInterface $clause,
                ?int $limit,
                ?int $offset,
                FilterInterface ...$filters
            ): \Generator {
                yield 'clause' => $clause;
                yield 'limit' => $limit;
                yield 'offset' => $offset;
                yield 'filters' => $filters;
            }
        };

        return new Searcher($clause_factory, $filter_factory, $repository);
    }

    public function testGetFilter(): void
    {
        $searcher = $this->getSearcher();

        $filter = $searcher->getFilter(56, 98, 'type');
        $this->assertSame(
            ['obj_id' => 56, 'sub_id' => 98, 'type' => 'type'],
            $filter->data
        );
    }

    public function testGetFilterWithObjIDNull(): void
    {
        $searcher = $this->getSearcher();

        $filter = $searcher->getFilter(null, 98, 'type');
        $this->assertSame(
            ['obj_id' => null, 'sub_id' => 98, 'type' => 'type'],
            $filter->data
        );
    }

    public function testGetFilterWithSubIDNull(): void
    {
        $searcher = $this->getSearcher();

        $filter = $searcher->getFilter(56, null, 'type');
        $this->assertSame(
            ['obj_id' => 56, 'sub_id' => null, 'type' => 'type'],
            $filter->data
        );
    }

    public function testGetFilterWithTypeNull(): void
    {
        $searcher = $this->getSearcher();

        $filter = $searcher->getFilter(56, 98, null);
        $this->assertSame(
            ['obj_id' => 56, 'sub_id' => 98, 'type' => null],
            $filter->data
        );
    }

    public function testSearch(): void
    {
        $searcher = $this->getSearcher();
        $clause = new NullClause();

        $results = iterator_to_array($searcher->search($clause, null, null));
        $this->assertSame(
            ['clause' => $clause, 'limit' => null, 'offset' => null, 'filters' => []],
            $results
        );
    }

    public function testSearchWithLimit(): void
    {
        $searcher = $this->getSearcher();
        $clause = new NullClause();

        $results = iterator_to_array($searcher->search($clause, 999, null));
        $this->assertSame(
            ['clause' => $clause, 'limit' => 999, 'offset' => null, 'filters' => []],
            $results
        );
    }

    public function testSearchWithLimitAndOffset(): void
    {
        $searcher = $this->getSearcher();
        $clause = new NullClause();

        $results = iterator_to_array($searcher->search($clause, 999, 333));
        $this->assertSame(
            ['clause' => $clause, 'limit' => 999, 'offset' => 333, 'filters' => []],
            $results
        );
    }

    public function testSearchWithFilters(): void
    {
        $searcher = $this->getSearcher();
        $clause = new NullClause();
        $filter_1 = new NullFilter();
        $filter_2 = new NullFilter();
        $filter_3 = new NullFilter();

        $results = iterator_to_array($searcher->search($clause, 999, 333, $filter_1, $filter_2, $filter_3));
        $this->assertSame(
            ['clause' => $clause, 'limit' => 999, 'offset' => 333, 'filters' => [$filter_1, $filter_2, $filter_3]],
            $results
        );
    }
}
