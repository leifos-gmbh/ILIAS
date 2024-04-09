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
use ILIAS\MetaData\Repository\Utilities\Queries\TableNamesHandler;
use ILIAS\MetaData\Elements\Structure\StructureSetInterface;
use ILIAS\MetaData\Repository\Dictionary\DictionaryInterface;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Paths\Navigator\StructureNavigatorInterface;
use ILIAS\MetaData\Repository\Dictionary\TagInterface;
use ILIAS\MetaData\Paths\Filters\FilterInterface as PathFilter;
use ILIAS\MetaData\Paths\Filters\FilterType;

class DatabasePathsParser implements DatabasePathsParserInterface
{
    use TableNamesHandler;

    protected const JOIN_TABLE = 'join_table';
    protected const JOIN_CONDITION = 'join_condition';
    protected const COLUMN_NAME = 'column_name';

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

    public function forSearch(PathInterface ...$paths): DatabaseParsedPathsInterface
    {
        $column_names = [];
        $joins = [];

        $path_number = 1;
        foreach ($paths as $path) {
            $navigator = $this->navigator_factory->structureNavigator(
                $path,
                $this->structure->getRoot()
            );
            $tables = [];
            $conditions = [];
            foreach ($this->collectJoinInfos($navigator, $path_number) as $type => $info) {
                if ($type === self::JOIN_TABLE) {
                    $tables[] = $info;
                }
                if ($type === self::JOIN_CONDITION) {
                    $conditions[] = $info;
                }
                if ($type === self::COLUMN_NAME) {
                    $column_names[] = new ColumnNameAssignment($path, $info);
                }
            }

            $joins[] = implode(' JOIN ', $tables) . ' ON ' . implode(' AND ', $conditions);
        }

        $select = 'SELECT DISTINCT p1t1.rbac_id, p1t1.obj_id, p1t1.obj_type FROM (' .
            implode(' JOIN ', $joins) . ')';
        return new DatabaseParsedPaths($select, ...$column_names);
    }

    /**
     * @return string[], key is either self::JOIN_TABLE, self::JOIN_CONDITION or self::COLUMN_NAME
     */
    protected function collectJoinInfos(
        StructureNavigatorInterface $navigator,
        int $path_number
    ): \Generator {
        $parent_tables_aliases = [];
    }

    protected function getBaseJoinConditionsForTable(
        string $table_alias,
        string $parent_table_alias = null,
        string $parent_table = null,
        string $parent_type = null
    ): string {
        $table_alias = $this->db->quoteIdentifier($table_alias);
        $conditions = [];

        if ($table_alias !== 'p1t1') {
            $conditions[] = 'p1t1.rbac_id = ' . $table_alias . '.rbac_id';
            $conditions[] = 'p1t1.obj_id = ' . $table_alias . '.obj_id';
            $conditions[] = 'p1t1.obj_type = ' . $table_alias . '.obj_type';
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

    protected function getJoinConditionFromPathFilter(
        string $table_alias,
        TagInterface $tag,
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
                $column = $table_alias . '.' . $this->db->quoteIdentifier($this->IDName($tag->table()));
                return $column . ' IN (' . implode(', ', $quoted_values) . ')';
                break;

            case FilterType::INDEX:
                // not supported
                return '';

            case FilterType::DATA:
                $column = "''";
                if ($tag->hasData()) {
                    $column = $table_alias . '.' . $this->db->quoteIdentifier($tag->dataField());
                }
                return $column . ' IN (' . implode(', ', $quoted_values) . ')';
                break;

            default:
                throw new \ilMDRepositoryException('Unknown filter type: ' . $filter->type()->value);
        }
    }
}
