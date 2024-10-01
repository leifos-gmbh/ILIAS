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

namespace ILIAS\AdvancedMetaData\Record\File\Repository\Element;

use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\CollectionInterface as ilAMDRecordFileRepositoryElementCollectionInterface;
use ILIAS\AdvancedMetaData\Record\File\Repository\Element\Collection as ilAMDRecordFileRepositoryElementCollection;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\FactoryInterface as ilAMDRecordFileRepositoryElementFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\HandlerInterface as ilAMDRecordFileRepositoryElementInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\Wrapper\FactoryInterface as ilAMDRecordFileRepositoryElementWrapperFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\Repository\Element\Handler as ilAMDRecordFileRepositoryElement;
use ILIAS\AdvancedMetaData\Record\File\Repository\Element\Wrapper\Factory as ilAMDRecordFileRepositoryElementWrapperFactory;
use ILIAS\ResourceStorage\Services as ilResourceStorageServices;

class Factory implements ilAMDRecordFileRepositoryElementFactoryInterface
{
    protected ilResourceStorageServices $irss;

    public function __construct(
        ilResourceStorageServices $irss
    ) {
        $this->irss = $irss;
    }

    public function handler(): ilAMDRecordFileRepositoryElementInterface
    {
        return new ilAMDRecordFileRepositoryElement(
            $this->wrapper()->irss()
        );
    }

    public function collection(): ilAMDRecordFileRepositoryElementCollectionInterface
    {
        return new ilAMDRecordFileRepositoryElementCollection();
    }

    public function wrapper(): ilAMDRecordFileRepositoryElementWrapperFactoryInterface
    {
        return new ilAMDRecordFileRepositoryElementWrapperFactory(
            $this->irss
        );
    }
}
