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

namespace ILIAS\Export\ExportHandler\I\Repository;

use ILIAS\Data\ObjectId;
use ILIAS\Export\ExportHandler\I\Info\Export\ilHandlerInterface as ilExportHandlerExportInfoInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\ilCollectionInterface as ilExportHandlerRepositoryElementCollectionInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\ilHandlerInterface as ilExportHandlerRepositoryElementInterface;
use ILIAS\Export\ExportHandler\I\Repository\ilResourceStakeholderInterface as ilExportHandlerRepositoryResourceStakeholderInterface;
use ILIAS\Export\ExportHandler\I\Repository\Key\ilCollectionInterface as ilExportHandlerRepositoryKeyCollectionInterface;

interface ilHandlerInterface
{
    public function createElement(
        ObjectId $object_id,
        ilExportHandlerExportInfoInterface $info,
        ilExportHandlerRepositoryResourceStakeholderInterface $stakeholder
    ): ilExportHandlerRepositoryElementInterface;

    public function storeElement(
        ilExportHandlerRepositoryElementInterface $element
    ): void;

    public function deleteElements(
        ilExportHandlerRepositoryKeyCollectionInterface $keys,
        ilExportHandlerRepositoryResourceStakeholderInterface $stakeholder
    ): void;

    public function getElements(
        ilExportHandlerRepositoryKeyCollectionInterface $keys
    ): ilExportHandlerRepositoryElementCollectionInterface;
}
