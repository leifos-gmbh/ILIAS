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

namespace ILIAS\Export\ExportHandler\PublicAccess\Repository\Wrapper\DB;

use ilDBInterface;
use ILIAS\Export\ExportHandler\I\ilFactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Wrapper\DB\ilFactoryInterface as ilExportHandlerPublicAccessRepositoryDBWrapperFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Wrapper\DB\ilHandlerInterface as ilExportHandlerPublicAccessRepositoryDBWrapperInterface;
use ILIAS\Export\ExportHandler\PublicAccess\Repository\Wrapper\DB\ilHandler as ilExportHandlerPublicAccessRepositoryDBWrapper;

class ilFactory implements ilExportHandlerPublicAccessRepositoryDBWrapperFactoryInterface
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

    public function handler(): ilExportHandlerPublicAccessRepositoryDBWrapperInterface
    {
        return new ilExportHandlerPublicAccessRepositoryDBWrapper(
            $this->db,
            $this->export_handler->publicAccess()->repository()->element(),
            $this->export_handler->publicAccess()->repository()->key(),
            $this->export_handler->publicAccess()->repository()->values(),
        );
    }
}
