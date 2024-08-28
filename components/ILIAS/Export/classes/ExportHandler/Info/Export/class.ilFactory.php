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

namespace ILIAS\Export\ExportHandler\Info\Export;

use ILIAS\Export\ExportHandler\I\ilFactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Component\ilFactoryInterface as ilExportHandlerExportComponentInfoFactoryInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Container\ilFactoryInterface as ilExportHandlerContainerExportInfoFactoryInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\ilCollectionInterface as ilExportHandlerExportInfoCollectionInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\ilFactoryInterface as ilExportHandlerExportInfoFactory;
use ILIAS\Export\ExportHandler\I\Info\Export\ilHandlerInterface as ilExportHandlerExportInfoInterface;
use ILIAS\Export\ExportHandler\Info\Export\Component\ilFactory as ilExportHandlerExportComponentInfoFactory;
use ILIAS\Export\ExportHandler\Info\Export\Container\ilFactory as ilExportHandlerContainerExportInfoFactory;
use ILIAS\Export\ExportHandler\Info\Export\ilCollection as ilExportHandlerExportInfoCollection;
use ILIAS\Export\ExportHandler\Info\Export\ilHandler as ilExportHandlerExportInfo;

class ilFactory implements ilExportHandlerExportInfoFactory
{
    protected ilExportHandlerFactoryInterface $export_handler;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler
    ) {
        $this->export_handler = $export_handler;
    }

    public function handler(): ilExportHandlerExportInfoInterface
    {
        return new ilExportHandlerExportInfo($this->export_handler);
    }

    public function collection(): ilExportHandlerExportInfoCollectionInterface
    {
        return new ilExportHandlerExportInfoCollection();
    }

    public function component(): ilExportHandlerExportComponentInfoFactoryInterface
    {
        return new ilExportHandlerExportComponentInfoFactory($this->export_handler);
    }

    public function container(): ilExportHandlerContainerExportInfoFactoryInterface
    {
        return new ilExportHandlerContainerExportInfoFactory($this->export_handler);
    }
}
