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

namespace ILIAS\AdvancedMetaData\Record\File\I\Repository;

use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\FactoryInterface as ilAMDRecordFileRepositoryElementFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\HandlerInterface as ilAMDRecordFileRepositoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Key\FactoryInterface as ilAMDRecordFileRepositoryKeyFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Stakeholder\FactoryInterface as ilAMDRecordFileRepositoryStakeholderFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Values\FactoryInterface as ilAMDRecordFileRepositoryValuesFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Wrapper\FactoryInterface as ilAMDRecordFileRepositoryWrapperFactoryInterface;

interface FactoryInterface
{
    public function handler(): ilAMDRecordFileRepositoryInterface;

    public function element(): ilAMDRecordFileRepositoryElementFactoryInterface;

    public function key(): ilAMDRecordFileRepositoryKeyFactoryInterface;

    public function stakeholder(): ilAMDRecordFileRepositoryStakeholderFactoryInterface;

    public function values(): ilAMDRecordFileRepositoryValuesFactoryInterface;

    public function wrapper(): ilAMDRecordFileRepositoryWrapperFactoryInterface;
}
