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

namespace ILIAS\MetaData\Editor\Full\Services\Tables;

use ILIAS\UI\Component\Table\DataRetrieval as DataRetrievalInterface;
use ILIAS\Data\Order;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;

class DataRetrieval implements DataRetrievalInterface
{
    protected array $raw_rows;

    public function __construct(array $raw_rows)
    {
        $this->raw_rows = $raw_rows;
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): \Generator {
        $records = $this->raw_rows;
        if ($range) {
            $records = array_slice($records, $range->getStart(), $range->getLength());
        }

        foreach ($records as $id => $record) {
            $disable_delete = $record['disable_delete'];
            unset($record['disable_delete']);
            yield $row_builder->buildDataRow((string) $id, $record)
                              ->withDisabledAction('delete', $disable_delete);
        }
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return count($this->raw_rows);
    }
}
