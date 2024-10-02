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

namespace ILIAS\AdvancedMetaData\Services\SubObjectModes;

use ILIAS\DI\Container;
use ILIAS\AdvancedMetaData\Services\SubObjectIDInterface;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\AdvancedMetaData\Services\SubObjectModes\DataTable;
use ILIAS\AdvancedMetaData\Services\SubObjectModes\Filter;

class SubObjectModes implements SubObjectModesInterface
{
    protected Container $dic;

    protected string $type;
    protected int $ref_id;

    /**
     * @var string[]
     */
    protected array $sub_types;

    public function __construct(
        Container $dic,
        string $type,
        int $ref_id,
        string ...$sub_types
    ) {
        $this->dic = $dic;
        $this->type = $type;
        $this->ref_id = $ref_id;
        $this->sub_types = $sub_types;
    }
    public function inDataTable(): DataTable\SupplierInterface
    {
        return new DataTable\Supplier(
            $this->dic->user(),
            $this->dic->ui()->factory(),
            new DataFactory(),
            $this->dic['static_url'],
            $this->type,
            $this->ref_id,
            ...$this->sub_types
        );
    }

    public function inFilter(): Filter\SupplierInterface
    {
        return new Filter\Supplier(
            $this->dic->user(),
            $this->dic->language(),
            $this->dic->ui()->factory(),
            new DataFactory(),
            $this->dic['static_url'],
            $this->type,
            $this->ref_id,
            ...$this->sub_types
        );
    }
}
