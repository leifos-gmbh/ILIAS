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

namespace ILIAS\Export\ExportHandler\Repository\Element;

use ILIAS\Export\ExportHandler\I\Repository\Element\ilHandlerInterface as ilExportHandlerRepositoryElementInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\Wrapper\IRSS\ilFactoryInterface as ilExportHandlerRepositoryElementIRSSWrapperFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\Wrapper\IRSS\ilHandlerInterface as ilExportHandlerRepositoryElementIRSSWrapperInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\Wrapper\IRSSInfo\ilFactoryInterface as ilExportHandlerRepositoryElementIRSSInfoWrapperFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\Wrapper\IRSSInfo\ilHandlerInterface as ilExportHandlerRepositoryElementIRSSInfoWrapperInterface;
use ILIAS\Export\ExportHandler\I\Repository\Key\ilHandlerInterface as ilExportHandlerRepositoryKeyInterface;
use ILIAS\Export\ExportHandler\I\Repository\Values\ilHandlerInterface as ilExportHandlerRepositoryValuesInterface;

class ilHandler implements ilExportHandlerRepositoryElementInterface
{
    protected ilExportHandlerRepositoryElementIRSSWrapperFactoryInterface $irss_factory;
    protected ilExportHandlerRepositoryElementIRSSInfoWrapperFactoryInterface $irss_info_factory;
    protected ilExportHandlerRepositoryKeyInterface $key;
    protected ilExportHandlerRepositoryValuesInterface $values;

    public function __construct(
        ilExportHandlerRepositoryElementIRSSWrapperFactoryInterface $irss_factory,
        ilExportHandlerRepositoryElementIRSSInfoWrapperFactoryInterface $irss_info_factory
    ) {
        $this->irss_factory = $irss_factory;
        $this->irss_info_factory = $irss_info_factory;
    }

    public function withKey(ilExportHandlerRepositoryKeyInterface $key): ilExportHandlerRepositoryElementInterface
    {
        $clone = clone $this;
        $clone->key = $key;
        return $clone;
    }

    public function withValues(ilExportHandlerRepositoryValuesInterface $values): ilExportHandlerRepositoryElementInterface
    {
        $clone = clone $this;
        $clone->values = $values;
        return $clone;
    }

    public function getKey(): ilExportHandlerRepositoryKeyInterface
    {
        return $this->key;
    }

    public function getValues(): ilExportHandlerRepositoryValuesInterface
    {
        return $this->values;
    }

    public function getIRSSInfo(): ilExportHandlerRepositoryElementIRSSInfoWrapperInterface
    {
        return $this->irss_info_factory->handler()->withResourceIdSerialized($this->key->getResourceIdSerialized());
    }

    public function getIRSS(): ilExportHandlerRepositoryElementIRSSWrapperInterface
    {
        return $this->irss_factory->handler()->withResourceIdSerialized($this->key->getResourceIdSerialized());
    }

    public function getFileType(): string
    {
        return self::ELEMENT_TYPE;
    }

    public function isStorable(): bool
    {
        return (
            isset($this->key) and
            isset($this->values) and
            $this->key->isCompleteKey() and
            $this->values->isValid()
        );
    }
}
