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

namespace ILIAS\Export\ExportHandler\I\Info\Export;

use ILIAS\Data\ObjectId;
use ILIAS\Export\ExportHandler\I\Info\Export\Component\ilCollectionInterface as ilExportHandlerExportComponentInfoCollectionInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Component\ilHandlerInterface as ilExportHandlerExportComponentInfoInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Container\ilHandlerInterface as ilExportHandlerContainerExportInfoInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\ilHandlerInterface as ilExportHandlerExportInfoInterface;
use ILIAS\Export\ExportHandler\I\Target\ilHandlerInterface as ilExportHandlerTargetInterface;

interface ilHandlerInterface
{
    public function withTarget(
        ilExportHandlerTargetInterface $export_target,
        int $timestamp
    ): ilHandlerInterface;

    public function withSetNumber(
        int $set_number
    ): ilHandlerInterface;

    public function withReuseExport(
        bool $reuse_export
    ): ilExportHandlerExportInfoInterface;

    public function getResueExport(): bool;

    public function getTarget(): ilExportHandlerTargetInterface;

    public function getTargetObjectId(): ObjectId;

    public function withContainerExportInfo(
        ilExportHandlerContainerExportInfoInterface $container_export_info
    ): ilHandlerInterface;

    public function getComponentCount(
        ilExportHandlerExportComponentInfoInterface $component_info
    );

    public function getComponentInfos(): ilExportHandlerExportComponentInfoCollectionInterface;

    public function getExportFolderName(): string;

    public function getZipFileName(): string;

    public function getHTTPPath(): string;

    public function getInstallationId(): string;

    public function getInstallationUrl(): string;

    public function getSetNumber(): int;
}
