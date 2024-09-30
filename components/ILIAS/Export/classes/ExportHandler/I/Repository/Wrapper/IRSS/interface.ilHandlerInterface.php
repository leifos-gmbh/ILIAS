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

namespace ILIAS\Export\ExportHandler\I\Repository\Wrapper\IRSS;

use DateTimeImmutable;
use ILIAS\Export\ExportHandler\I\Info\Export\ilHandlerInterface as ilExportHandlerExportInfoInterface;
use ILIAS\Export\ExportHandler\I\Repository\ilResourceStakeholderInterface as ilExportHandlerRepositoryResourceStakeholderInterface;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

interface ilHandlerInterface
{
    public const TMP_FILE_PATH = "tmp_file_ztopslcaneadw";
    public const TMP_FILE_CONTENT = "tmp_file_content";

    public function createEmptyContainer(
        ilExportHandlerExportInfoInterface $info,
        ilExportHandlerRepositoryResourceStakeholderInterface $stakeholder
    ): ResourceIdentification;

    public function getCreationDate(ResourceIdentification $resource_id): DateTimeImmutable;

    public function removeContainer(
        string $resource_identification_serialized,
        ilExportHandlerRepositoryResourceStakeholderInterface $stakeholder
    ): bool;
}
