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

namespace ILIAS\MetaData\Repository\Utilities\Queries\Paths;

use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Elements\Structure\StructureSetInterface;
use ILIAS\MetaData\Repository\Dictionary\DictionaryInterface;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Paths\Navigator\StructureNavigatorInterface;
use ILIAS\MetaData\Paths\Steps\StepToken;
use ILIAS\MetaData\Elements\Data\Type;
use ILIAS\MetaData\Vocabularies\Dictionary\LOMDictionaryInitiator as LOMVocabInitiator;
use ILIAS\MetaData\Paths\Filters\FilterInterface as PathFilter;
use ILIAS\MetaData\Paths\Filters\FilterType;
use ILIAS\MetaData\Repository\Utilities\Queries\TableNamesHandler;

class DatabasePathsParser implements DatabasePathsParserInterface
{
    use TableNamesHandler;

    protected const JOIN_TABLE = 'join_table';
    protected const JOIN_CONDITION = 'join_condition';
    protected const COLUMN_NAME = 'column_name';

    /**
     * @var string[]
     */
    protected array $path_joins = [];

    protected int $path_number = 1;

    /**
     * Just for quoting.
     */
    protected \ilDBInterface $db;
    protected StructureSetInterface $structure;
    protected DictionaryInterface $dictionary;
    protected NavigatorFactoryInterface $navigator_factory;

    public function __construct(
        \ilDBInterface $db,
        StructureSetInterface $structure,
        DictionaryInterface $dictionary,
        NavigatorFactoryInterface $navigator_factory,
    ) {
        $this->db = $db;
        $this->structure = $structure;
        $this->dictionary = $dictionary;
        $this->navigator_factory = $navigator_factory;
    }

    /**
     * Make sure that you add paths before calling this.
     */
    public function getSelectForQuery(): string
    {
        $from_expression = '';
        if (empty($this->path_joins)) {
            throw new \ilMDRepositoryException('No tables found for search.');
        } elseif (count($this->path_joins) === 1) {
            $from_expression = $this->path_joins[0];
        } else {
            $from_expression = 'il_meta_general AS base';
            $path_number = 1;
            foreach ($this->path_joins as $join) {
                $condition = $this->getBaseJoinConditionsForTable(
                    'base',
                    'p' . $path_number . 't1',
                );
                $from_expression .= ' LEFT JOIN (' . $join . ') ON ' . $condition;
                $path_number++;
            }
        }

        return 'SELECT DISTINCT p1t1.rbac_id, p1t1.obj_id, p1t1.obj_type FROM (' . $from_expression . ')';
    }

    public function addPathAndGetColumn(PathInterface $path): string
    {
        $data_column_name = '';

        $navigator = $this->navigator_factory->structureNavigator(
            $path,
            $this->structure->getRoot()
        );
        $tables = [];
        $conditions = [];
        foreach ($this->collectJoinInfos($navigator, $this->path_number) as $type => $info) {
            if ($type === self::JOIN_TABLE && !empty($info)) {
                $tables[] = $info;
            }
            if ($type === self::JOIN_CONDITION && !empty($info)) {
                $conditions[] = $info;
            }
            if ($type === self::COLUMN_NAME && !empty($info)) {
                $data_column_name = $info;
            }
        }

        if (!empty($tables)) {
            $join = implode(' JOIN ', $tables);
            if (!empty($conditions)) {
                $join .= ' ON ' . implode(' AND ', $conditions);
            }
            $this->path_joins[] = $join;
            $this->path_number++;
        }

        return $data_column_name;
    }

    public function getTableAliasForFilters(): ?string
    {
        return 'p1t1';
    }

    /**
     * @return string[], key is either self::JOIN_TABLE, self::JOIN_CONDITION or self::COLUMN_NAME
     */
    protected function collectJoinInfos(
        StructureNavigatorInterface $navigator,
        int $path_number
    ): \Generator {
        $table_aliases = [];
        $current_tag = null;
        $current_table = '';
        $table_number = 1;

        while ($navigator->hasNextStep()) {
            $navigator = $navigator->nextStep();
            $current_tag = $this->dictionary->tagForElement($navigator->element());

            if ($current_tag?->table() && $current_table !== $current_tag?->table()) {
                $parent_table = $current_table;
                $current_table = $current_tag->table();
                $this->checkTable($current_table);

                /**
                 * If the step goes back to a previous table, reuse the same
                 * alias, but if it goes down again to the same table, use a new
                 * alias (since path filter might mean you're on different
                 * branches now).
                 */
                if ($navigator->currentStep()->name() === StepToken::SUPER) {
                    $alias = $table_aliases[$current_table];
                } else {
                    $alias = 'p' . $path_number . 't' . $table_number;
                    $table_aliases[$current_table] = $alias;
                    $table_number++;

                    yield self::JOIN_TABLE => $this->db->quoteIdentifier($this->table($current_table)) .
                        ' AS ' . $this->db->quoteIdentifier($alias);
                }

                if (!$current_tag->hasParent()) {
                    yield self::JOIN_CONDITION => $this->getBaseJoinConditionsForTable(
                        'p' . $path_number . 't1',
                        $alias
                    );
                    continue;
                }

                yield self::JOIN_CONDITION => $this->getBaseJoinConditionsForTable(
                    'p' . $path_number . 't1',
                    $alias,
                    $table_aliases[$parent_table],
                    $parent_table,
                    $current_tag->parent()
                );
            }

            foreach ($navigator->currentStep()->filters() as $filter) {
                yield self::JOIN_CONDITION => $this->getJoinConditionFromPathFilter(
                    $table_aliases[$current_table],
                    $current_table,
                    $current_tag?->hasData() ? $current_tag->dataField() : null,
                    $navigator->element()->getDefinition()->dataType() === Type::VOCAB_SOURCE ?
                        LOMVocabInitiator::SOURCE :
                        '',
                    $filter
                );
            }
        }

        $final_table_alias = $this->db->quoteIdentifier($table_aliases[$current_table]);
        if ($current_tag?->hasData()) {
            yield self::COLUMN_NAME => 'COALESCE(' . $this->db->quoteIdentifier($table_aliases[$current_table]) .
                '.' . $this->db->quoteIdentifier($current_tag->dataField()) . ", '')";
            return;
        }
        yield self::COLUMN_NAME => "''";
    }

    protected function getBaseJoinConditionsForTable(
        string $first_table_alias,
        string $table_alias,
        string $parent_table_alias = null,
        string $parent_table = null,
        string $parent_type = null
    ): string {
        $table_alias = $this->db->quoteIdentifier($table_alias);
        $first_table_alias = $this->db->quoteIdentifier($first_table_alias);
        $conditions = [];

        if ($table_alias !== $first_table_alias) {
            $conditions[] = $first_table_alias . '.rbac_id = ' . $table_alias . '.rbac_id';
            $conditions[] = $first_table_alias . '.obj_id = ' . $table_alias . '.obj_id';
            $conditions[] = $first_table_alias . '.obj_type = ' . $table_alias . '.obj_type';
        }

        if (!is_null($parent_table_alias) && !is_null($parent_table)) {
            $parent_id_column = $parent_table_alias . '.' .
                $this->db->quoteIdentifier($this->IDName($parent_table));
            $conditions[] = $parent_id_column . ' = ' . $table_alias . '.parent_id';
        }
        if (!is_null($parent_type)) {
            $conditions[] = $this->db->quote($parent_type, \ilDBConstants::T_TEXT) .
                ' = ' . $table_alias . '.parent_type';
        }

        return implode(' AND ', $conditions);
    }

    /**
     * Direct_data is only needed to make vocab sources work until
     * controlled vocabularies are implemented.
     */
    protected function getJoinConditionFromPathFilter(
        string $table_alias,
        string $table,
        ?string $data_field,
        string $direct_data,
        PathFilter $filter
    ): string {
        $table_alias = $this->db->quoteIdentifier($table_alias);
        $quoted_values = [];
        foreach ($filter->values() as $value) {
            $type = $filter->type() === FilterType::DATA ? \ilDBConstants::T_TEXT : \ilDBConstants::T_INTEGER;
            $quoted_values[] = $this->db->quote($value, $type);
        }

        if (empty($quoted_values)) {
            return '';
        }

        switch ($filter->type()) {
            case FilterType::NULL:
                return '';

            case FilterType::MDID:
                $column = $table_alias . '.' . $this->db->quoteIdentifier($this->IDName($table));
                return $column . ' IN (' . implode(', ', $quoted_values) . ')';
                break;

            case FilterType::INDEX:
                // not supported
                return '';

            case FilterType::DATA:
                $column = $this->db->quote($direct_data, \ilDBConstants::T_TEXT);
                if (!is_null($data_field)) {
                    $column = 'COALESCE(' . $table_alias . '.' . $this->db->quoteIdentifier($data_field) . ", '')";
                }
                return $column . ' IN (' . implode(', ', $quoted_values) . ')';
                break;

            default:
                throw new \ilMDRepositoryException('Unknown filter type: ' . $filter->type()->value);
        }
    }
}
