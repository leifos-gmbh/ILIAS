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

namespace ILIAS\MetaData\Repository\Utilities\Queries;

use ILIAS\MetaData\Repository\Search\Filters\FilterInterface;
use ILIAS\MetaData\Elements\RessourceID\RessourceIDFactoryInterface;
use ILIAS\MetaData\Repository\Search\Clauses\ClauseInterface;
use ILIAS\MetaData\Repository\Search\Operator;
use ILIAS\MetaData\Repository\Search\Mode;
use ILIAS\MetaData\Repository\Search\Clauses\Properties\BasicProperties;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Repository\Utilities\Queries\Paths\DatabasePathsParserFactoryInterface;
use ILIAS\MetaData\Repository\Utilities\Queries\Paths\DatabasePathsParserInterface;

class DatabaseSearcher implements DatabaseSearcherInterface
{
    protected RessourceIDFactoryInterface $ressource_factory;
    protected DatabasePathsParserFactoryInterface $paths_parser_factory;
    protected \ilDBInterface $db;
    protected \ilLogger $logger;

    public function __construct(
        RessourceIDFactoryInterface $ressource_factory,
        DatabasePathsParserFactoryInterface $paths_parser_factory,
        \ilDBInterface $db,
        \ilLogger $logger
    ) {
        $this->ressource_factory = $ressource_factory;
        $this->paths_parser_factory = $paths_parser_factory;
        $this->db = $db;
        $this->logger = $logger;
    }

    public function search(
        ClauseInterface $clause,
        ?int $limit,
        ?int $offset,
        FilterInterface ...$filters
    ): \Generator {
        $paths_parser = $this->paths_parser_factory->forSearch();
        $where = $this->getClauseForQueryWhere($clause, $paths_parser);

        $result = $this->db->query(
            $query = $paths_parser->getSelectForQuery() . ' WHERE ' . $where .
            $this->getFiltersForQueryWhere($paths_parser->getTableAliasForFilters(), ...$filters) .
            ' ORDER BY rbac_id, obj_id, obj_type' . $this->getLimitAndOffsetForQuery($limit, $offset)
        );

        global $DIC;
        $DIC->logger()->root()->dump($query);

        while ($row = $this->db->fetchAssoc($result)) {
            yield $this->ressource_factory->ressourceID(
                (int) $row['rbac_id'],
                (int) $row['obj_id'],
                (string) $row['obj_type']
            );
        }
    }

    protected function getFiltersForQueryWhere(
        string $table_alias,
        FilterInterface ...$filters
    ): string {
        if (empty($filters)) {
            return '';
        }

        $quoted_table_alias = $this->db->quoteIdentifier($table_alias);

        $filter_where = [];
        foreach ($filters as $filter) {
            $filter_values = [];
            if (!is_null($filter->objID())) {
                $filter_values[] = $quoted_table_alias . '.rbac_id = ' .
                    $this->db->quote($filter->objID(), \ilDBConstants::T_INTEGER);
            }
            if (!is_null($filter->subID())) {
                $filter_values[] = $quoted_table_alias . '.obj_id = ' .
                    $this->db->quote($filter->subID(), \ilDBConstants::T_INTEGER);
            }
            if (!is_null($filter->type())) {
                $filter_values[] = $quoted_table_alias . '.obj_type = ' .
                    $this->db->quote($filter->type(), \ilDBConstants::T_TEXT);
            }
            $filter_where[] = '(' . implode(' AND ', $filter_values) . ')';
        }
        return ' AND (' . implode(' OR ', $filter_where) . ')';
    }

    protected function getLimitAndOffsetForQuery(?int $limit, ?int $offset): string
    {
        $query_limit = '';
        if (!is_null($limit) || !is_null($offset)) {
            $limit = is_null($limit) ? PHP_INT_MAX : $limit;
            $query_limit = ' LIMIT ' . $this->db->quote($limit, \ilDBConstants::T_INTEGER);
        }
        $query_offset = '';
        if (!is_null($offset)) {
            $query_offset = ' OFFSET ' . $this->db->quote($offset, \ilDBConstants::T_INTEGER);
        }
        return $query_limit . $query_offset;
    }

    protected function getClauseForQueryWhere(
        ClauseInterface $clause,
        DatabasePathsParserInterface $paths_parser,
        int $depth = 0
    ): string {
        if ($depth > 50) {
            throw new \ilMDRepositoryException('Search clause is nested to deep.');
        }

        if (!$clause->isJoin()) {
            return $this->getBasicClauseForQueryWhere(
                $clause->basicProperties(),
                $clause->isNegated(),
                $paths_parser
            );
        }

        $join_props = $clause->joinProperties();

        $sub_clauses_for_query = [];
        foreach ($join_props->subClauses() as $sub_clause) {
            $sub_clauses_for_query[] = $this->getClauseForQueryWhere($sub_clause, $paths_parser, $depth + 1);
        }

        switch ($join_props->operator()) {
            case Operator::AND:
                $operator_for_query = 'AND';
                break;

            case Operator::OR:
                $operator_for_query = 'OR';
                break;

            default:
                throw new \ilMDRepositoryException('Invalid search operator.');
        }

        $negation = '';
        if ($clause->isNegated()) {
            $negation = 'NOT ';
        }

        return $negation . '(' . implode(' ' . $operator_for_query . ' ', $sub_clauses_for_query) . ')';
    }

    protected function getBasicClauseForQueryWhere(
        BasicProperties $basic_props,
        bool $is_negated,
        DatabasePathsParserInterface $paths_parser
    ): string {
        switch ($basic_props->mode()) {
            case Mode::EQUALS:
                $comparison = '= ' .
                    $this->db->quote($basic_props->value(), \ilDBConstants::T_TEXT);
                break;

            case Mode::CONTAINS:
                $comparison = 'LIKE ' .
                    $this->db->quote('%' . $basic_props->value() . '%', \ilDBConstants::T_TEXT);
                break;

            case Mode::STARTS_WITH:
                $comparison = 'LIKE ' .
                    $this->db->quote($basic_props->value() . '%', \ilDBConstants::T_TEXT);
                break;

            case Mode::ENDS_WITH:
                $comparison = 'LIKE ' .
                    $this->db->quote('%' . $basic_props->value(), \ilDBConstants::T_TEXT);
                break;

            default:
                throw new \ilMDRepositoryException('Invalid search mode.');
        }

        $negation = '';
        if ($is_negated) {
            $negation = 'NOT ';
        }

        return $negation . $paths_parser->addPathAndGetColumn($basic_props->path()) . ' ' . $comparison;
    }
}
