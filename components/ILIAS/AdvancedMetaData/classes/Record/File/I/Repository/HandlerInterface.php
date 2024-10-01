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

use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\HandlerInterface as ilAMDRecordFileRepositoryElementInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\CollectionInterface as ilAMDRecordFileRepositoryElementCollectionInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Key\HandlerInterface as ilAMDRecordFileRepositoryKeyInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Values\HandlerInterface as ilAMDRecordFileRepositoryValuesInterface;

interface HandlerInterface
{
    public function store(
        ilAMDRecordFileRepositoryKeyInterface $key,
        ilAMDRecordFileRepositoryValuesInterface $values
    ): void;

    public function getElements(
        ilAMDRecordFileRepositoryKeyInterface $key
    ): ilAMDRecordFileRepositoryElementCollectionInterface|null;

    public function delete(
        ilAMDRecordFileRepositoryKeyInterface $key
    ): void;
}
