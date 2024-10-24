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

namespace ILIAS\AdvancedMetaData\Record\File\Repository;

use ilDBInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\FactoryInterface as ilAMDRecordFileRepositoryElementFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\FactoryInterface as ilAMDRecordFileRepositoryFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\HandlerInterface as ilAMDRecordFileRepositoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Key\FactoryInterface as ilAMDRecordFileRepositoryKeyFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Stakeholder\FactoryInterface as ilAMDRecordFileRepositoryStakeholderFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\Repository\Stakeholder\Factory as ilAMDRecordFileRepositoryStakeholderFactory;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Values\FactoryInterface as ilAMDRecordFileRepositoryValuesFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Wrapper\FactoryInterface as ilAMDRecordFileRepositoryWrapperFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\Repository\Element\Factory as ilAMDRecordFileRepositoryElementFactory;
use ILIAS\AdvancedMetaData\Record\File\Repository\Handler as ilAMDRecordFileRepository;
use ILIAS\AdvancedMetaData\Record\File\Repository\Key\Factory as ilAMDRecordFileRepositoryKeyFactory;
use ILIAS\AdvancedMetaData\Record\File\Repository\Values\Factory as ilAMDRecordFileRepositoryValuesFactory;
use ILIAS\AdvancedMetaData\Record\File\Repository\Wrapper\Factory as ilAMDRecordFileRepositoryWrapperFactory;
use ILIAS\ResourceStorage\Services as ilResourceStorageServices;

class Factory implements ilAMDRecordFileRepositoryFactoryInterface
{
    protected ilDBInterface $db;
    protected ilResourceStorageServices $irss;

    public function __construct(
        ilDBInterface $db,
        ilResourceStorageServices $irss
    ) {
        $this->db = $db;
        $this->irss = $irss;
    }

    public function handler(): ilAMDRecordFileRepositoryInterface
    {
        return new ilAMDRecordFileRepository(
            $this->wrapper()->db()->handler()
        );
    }

    public function element(): ilAMDRecordFileRepositoryElementFactoryInterface
    {
        return new ilAMDRecordFileRepositoryElementFactory(
            $this->irss
        );
    }

    public function key(): ilAMDRecordFileRepositoryKeyFactoryInterface
    {
        return new ilAMDRecordFileRepositoryKeyFactory();
    }

    public function stakeholder(): ilAMDRecordFileRepositoryStakeholderFactoryInterface
    {
        return new ilAMDRecordFileRepositoryStakeholderFactory();
    }

    public function values(): ilAMDRecordFileRepositoryValuesFactoryInterface
    {
        return new ilAMDRecordFileRepositoryValuesFactory();
    }

    public function wrapper(): ilAMDRecordFileRepositoryWrapperFactoryInterface
    {
        return new ilAMDRecordFileRepositoryWrapperFactory(
            $this->db,
            $this->element(),
            $this->key(),
            $this->values()
        );
    }
}
