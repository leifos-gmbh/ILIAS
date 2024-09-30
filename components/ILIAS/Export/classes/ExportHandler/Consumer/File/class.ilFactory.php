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

namespace ILIAS\Export\ExportHandler\Consumer\File;

use ILIAS\Export\ExportHandler\Consumer\File\Identifier\ilFactory as ilExportHandlerConsumerFileIdentifierFactory;
use ILIAS\Export\ExportHandler\Consumer\File\ilCollectionBuilder as ilExportHandlerConsumerFileCollectionBuilder;
use ILIAS\Export\ExportHandler\I\Consumer\File\Identifier\ilFactoryInterface as ilExportHandlerConsumerFileIdentifierFactoryInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\ilCollectionBuilderInterface as ilExportHandlerConsumerFileCollectionBuilderInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\ilFactoryInterface as ilExportHandlerConsumerFileFactoryInterface;
use ILIAS\Export\ExportHandler\I\ilFactoryInterface as ilExportHandlerFactoryInterface;

class ilFactory implements ilExportHandlerConsumerFileFactoryInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler
    ) {
        $this->export_handler = $export_handler;
    }

    public function collectionBuilder(): ilExportHandlerConsumerFileCollectionBuilderInterface
    {
        return new ilExportHandlerConsumerFileCollectionBuilder(
            $this->export_handler->info()->file(),
            $this->export_handler->publicAccess()->handler()
        );
    }

    public function identifier(): ilExportHandlerConsumerFileIdentifierFactoryInterface
    {
        return new ilExportHandlerConsumerFileIdentifierFactory(
            $this->export_handler
        );
    }
}
