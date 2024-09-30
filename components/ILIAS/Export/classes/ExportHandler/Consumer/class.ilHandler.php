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

namespace ILIAS\Export\ExportHandler\Consumer;

use ILIAS\Data\ObjectId;
use ILIAS\Export\ExportHandler\I\Consumer\ilHandlerInterface as ilExportHandlerConsumerInterface;
use ILIAS\Export\ExportHandler\I\ilFactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Manager\ilHandlerInterface as ilExportHandlerManagerInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\ilHandlerInterface as ilExportHandlerPublicAccessInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\ilHandlerInterface as ilExportHandlerRepositoryElementInterface;

class ilHandler implements ilExportHandlerConsumerInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler
    ) {
        $this->export_handler = $export_handler;
    }

    public function publicAccess(): ilExportHandlerPublicAccessInterface
    {
        return $this->export_handler->publicAccess()->handler();
    }

    public function createStandardExport(
        int $user_id,
        ObjectId $object_id
    ): ilExportHandlerRepositoryElementInterface {
        $manager = $this->export_handler->manager()->handler();
        return $manager->createExport(
            $user_id,
            $manager->getExportInfo($object_id, time()),
            ""
        );
    }
}
