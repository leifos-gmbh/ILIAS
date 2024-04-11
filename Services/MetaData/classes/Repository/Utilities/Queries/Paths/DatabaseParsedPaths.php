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

class DatabaseParsedPaths implements DatabaseParsedPathsInterface
{
    /**
     * @var string[]
     */
    protected array $column_names_by_path;
    protected string $select;
    protected string $table_alias_for_filters;

    public function __construct(
        string $table_alias_for_filters,
        string $select,
        ColumnNameAssignment ...$colum_names
    ) {
        $this->table_alias_for_filters = $table_alias_for_filters;
        $this->select = $select;
        $this->column_names_by_path = [];
        foreach ($colum_names as $colum_name) {
            $this->column_names_by_path[$colum_name->path()->toString()] = $colum_name->columnName();
        }
    }

    public function selectForQuery(): string
    {
        return $this->select;
    }

    public function columnForPath(PathInterface $path): string
    {
        $path_string = $path->toString();
        if (!isset($this->column_names_by_path[$path_string])) {
            throw new \ilMDRepositoryException('No column found for path: ' . $path_string);
        }
        return $this->column_names_by_path[$path_string];
    }

    public function tableAliasForFilters(): string
    {
        return $this->table_alias_for_filters;
    }
}
