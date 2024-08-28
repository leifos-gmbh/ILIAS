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

namespace ILIAS\Export\ExportHandler\Repository\Element\Wrapper;

use ILIAS\Export\ExportHandler\I\ilFactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\Wrapper\ilFactoryInterface as ilExportHandlerRepositoryElementWrapperFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\Wrapper\IRSS\ilFactoryInterface as ilExportHandlerRepositoryElementIRSSWrapperFactoryInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\Wrapper\IRSSInfo\ilFactoryInterface as ilExportHandlerRepositoryElementIRSSInfoWrapperFactoryInterface;
use ILIAS\Export\ExportHandler\Repository\Element\Wrapper\IRSS\ilFactory as ilExportHandlerRepositoryElementIRSSWrapperFactory;
use ILIAS\Export\ExportHandler\Repository\Element\Wrapper\IRSSInfo\ilFactory as ilExportHandlerRepositoryElementIRSSInfoWrapperFactory;
use ILIAS\ResourceStorage\Services as ResourcesStorageService;

class ilFactory implements ilExportHandlerRepositoryElementWrapperFactoryInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;
    protected ResourcesStorageService $irss;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler,
        ResourcesStorageService $irss
    ) {
        $this->export_handler = $export_handler;
        $this->irss = $irss;
    }

    public function irss(): ilExportHandlerRepositoryElementIRSSWrapperFactoryInterface
    {
        return new ilExportHandlerRepositoryElementIRSSWrapperFactory(
            $this->export_handler,
            $this->irss
        );
    }

    public function irssInfo(): ilExportHandlerRepositoryElementIRSSInfoWrapperFactoryInterface
    {
        return new ilExportHandlerRepositoryElementIRSSInfoWrapperFactory(
            $this->export_handler,
            $this->irss
        );
    }
}
