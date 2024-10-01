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

namespace ILIAS\AdvancedMetaData\Record\File\Repository\Wrapper;

use ilDBInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\FactoryInterface as ilAMDRecordFileRepositoryElementFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Key\FactoryInterface as ilAMDRecordFileRepositoryKeyFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Values\FactoryInterface as ilAMDRecordFileRepositoryValuesFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Wrapper\DB\FactoryInterface as ilAMDRecordFileRepositoryDBWrapperFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Wrapper\FactoryInterface as ilAMDRecordFileRepositoryFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\Repository\Wrapper\DB\Factory as ilAMDRecordFileRepositoryDBWrapperFactory;

class Factory implements ilAMDRecordFileRepositoryFactoryInterface
{
    protected ilDBInterface $db;
    protected ilAMDRecordFileRepositoryElementFactoryInterface $element_factory;
    protected ilAMDRecordFileRepositoryKeyFactoryInterface $key_factory;
    protected ilAMDRecordFileRepositoryValuesFactoryInterface $values_factory;

    public function __construct(
        ilDBInterface $db,
        ilAMDRecordFileRepositoryElementFactoryInterface $element_factory,
        ilAMDRecordFileRepositoryKeyFactoryInterface $key_factory,
        ilAMDRecordFileRepositoryValuesFactoryInterface $values_factory
    ) {
        $this->db = $db;
        $this->element_factory = $element_factory;
        $this->key_factory = $key_factory;
        $this->values_factory = $values_factory;
    }

    public function db(): ilAMDRecordFileRepositoryDBWrapperFactoryInterface
    {
        return new ilAMDRecordFileRepositoryDBWrapperFactory(
            $this->db,
            $this->element_factory,
            $this->key_factory,
            $this->values_factory
        );
    }
}
