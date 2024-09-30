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

namespace ILIAS\Export\ExportHandler\Info\File;

use ILIAS\Export\ExportHandler\I\ilFactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Info\File\ilCollectionInterface as ilExportHandlerFileInfoCollectionInterface;
use ILIAS\Export\ExportHandler\I\Info\File\ilFactoryInterface as ilExportHandlerFileInfoFactoryInterface;
use ILIAS\Export\ExportHandler\I\Info\File\ilHandlerInterface as ilExportHandlerFileInfoInterface;
use ILIAS\Export\ExportHandler\Info\File\ilCollection as ilExportHandlerFileInfoCollection;
use ILIAS\Export\ExportHandler\Info\File\ilHandler as ilExportHandlerFileInfo;
use ILIAS\ResourceStorage\Services as ResourcesStorageService;

class ilFactory implements ilExportHandlerFileInfoFactoryInterface
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

    public function collection(): ilExportHandlerFileInfoCollectionInterface
    {
        return new ilExportHandlerFileInfoCollection();
    }

    public function handler(): ilExportHandlerFileInfoInterface
    {
        return new ilExportHandlerFileInfo($this->irss);
    }
}
