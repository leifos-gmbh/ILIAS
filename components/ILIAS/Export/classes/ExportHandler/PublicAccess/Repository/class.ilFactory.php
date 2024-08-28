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

namespace ILIAS\Export\ExportHandler\PublicAccess\Repository;

use ilDBInterface;
use ILIAS\Export\ExportHandler\I\ilFactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Element\ilFactoryInterface as ilExportHandlerPublicAccessRepositoryElementFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\ilFactoryInterface as ilExportHandlerPublicAccessRepositoryFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\ilHandlerInterface as ilExportHandlerPublicAccessRepositoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Key\ilFactoryInterface as ilExportHandlerPublicAccessRepositoryKeyFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Values\ilFactoryInterface as ilExportHandlerPublicAccessRepositoryValuesFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Wrapper\ilFactoryInterface as ilExportHandlerPublicAccessRepositoryWrapperFactoryInterface;
use ILIAS\Export\ExportHandler\PublicAccess\Repository\Element\ilFactory as ilExportHandlerPublicAccessRepositoryElementFactory;
use ILIAS\Export\ExportHandler\PublicAccess\Repository\ilHandler as ilExportHandlerPublicAccessRepository;
use ILIAS\Export\ExportHandler\PublicAccess\Repository\Key\ilFactory as ilExportHandlerPublicAccessRepositoryKeyFactory;
use ILIAS\Export\ExportHandler\PublicAccess\Repository\Values\ilFactory as ilExportHandlerPublicAccessRepositoryValuesFactory;
use ILIAS\Export\ExportHandler\PublicAccess\Repository\Wrapper\ilFactory as ilExportHandlerPublicAccessRepositoryWrapperFactory;

class ilFactory implements ilExportHandlerPublicAccessRepositoryFactoryInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;
    protected ilDBInterface $db;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler,
        ilDBInterface $db
    ) {
        $this->export_handler = $export_handler;
        $this->db = $db;
    }

    public function element(): ilExportHandlerPublicAccessRepositoryElementFactoryInterface
    {
        return new ilExportHandlerPublicAccessRepositoryElementFactory(
            $this->export_handler
        );
    }

    public function handler(): ilExportHandlerPublicAccessRepositoryInterface
    {
        return new ilExportHandlerPublicAccessRepository(
            $this->export_handler->publicAccess()->repository()->wrapper()->db()->handler(),
            $this->export_handler->publicAccess()->repository()->key()
        );
    }

    public function key(): ilExportHandlerPublicAccessRepositoryKeyFactoryInterface
    {
        return new ilExportHandlerPublicAccessRepositoryKeyFactory();
    }

    public function values(): ilExportHandlerPublicAccessRepositoryValuesFactoryInterface
    {
        return new ilExportHandlerPublicAccessRepositoryValuesFactory(
            $this->export_handler
        );
    }

    public function wrapper(): ilExportHandlerPublicAccessRepositoryWrapperFactoryInterface
    {
        return new ilExportHandlerPublicAccessRepositoryWrapperFactory(
            $this->export_handler,
            $this->db
        );
    }
}
