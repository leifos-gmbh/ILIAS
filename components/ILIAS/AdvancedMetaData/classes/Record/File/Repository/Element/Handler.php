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

use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\HandlerInterface as ilAMDRecordFileRepositoryElementInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\Wrapper\IRSS\HandlerInterface as ilAMDRecordFileRepositoryElementIRSSWrapperInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Element\Wrapper\IRSS\FactoryInterface as ilAMDRecordFileRepositoryElementIRSSWrapperFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Key\HandlerInterface as ilAMDRecordFileRepositoryKeyInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\Values\HandlerInterface as ilAMDRecordFileRepositoryValuesInterface;

class Handler implements ilAMDRecordFileRepositoryElementInterface
{
    protected ilAMDRecordFileRepositoryKeyInterface $key;
    protected ilAMDRecordFileRepositoryValuesInterface $values;
    protected ilAMDRecordFileRepositoryElementIRSSWrapperFactoryInterface $irss_wrapper_factory;

    public function __construct(
        ilAMDRecordFileRepositoryElementIRSSWrapperFactoryInterface $irss_wrapper_factory
    ) {
        $this->irss_wrapper_factory = $irss_wrapper_factory;
    }

    public function withKey(
        ilAMDRecordFileRepositoryKeyInterface $key
    ): ilAMDRecordFileRepositoryElementInterface {
        $clone = clone $this;
        $clone->key = $key;
        return $clone;
    }

    public function withValues(
        ilAMDRecordFileRepositoryValuesInterface $values
    ): ilAMDRecordFileRepositoryElementInterface {
        $clone = clone $this;
        $clone->values = $values;
        return $clone;
    }

    public function getKey(): ilAMDRecordFileRepositoryKeyInterface
    {
        return $this->key;
    }

    public function getValues(): ilAMDRecordFileRepositoryValuesInterface
    {
        return $this->values;
    }

    public function getIRSS(): ilAMDRecordFileRepositoryElementIRSSWrapperInterface
    {
        return $this->irss_wrapper_factory->handler()
            ->withResourceIdSerialized($this->key->getResourceIdSerialized());
    }
}
