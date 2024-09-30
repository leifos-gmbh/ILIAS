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

namespace ILIAS\Export\ExportHandler\I\Info\Export\Container\ObjectId;

use ILIAS\Export\ExportHandler\I\Info\Export\Container\ObjectId\ilCollectionBuilderInterface as ilExportHandlerContainerExportInfoObjectIdCollectionBuilderInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Container\ObjectId\ilCollectionInterface as ilExportHandlerContainerExportInfoObjectIdCollectionInterface;
use ILIAS\Export\ExportHandler\I\Info\Export\Container\ObjectId\ilHandlerInterface as ilExportHandlerContainerExportInfoObjectIdInterface;

interface ilFactoryInterface
{
    public function handler(): ilExportHandlerContainerExportInfoObjectIdInterface;

    public function collection(): ilExportHandlerContainerExportInfoObjectIdCollectionInterface;

    public function collectionBuilder(): ilExportHandlerContainerExportInfoObjectIdCollectionBuilderInterface;
}
