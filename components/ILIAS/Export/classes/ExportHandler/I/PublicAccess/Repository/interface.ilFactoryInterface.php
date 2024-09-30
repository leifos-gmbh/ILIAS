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

namespace ILIAS\Export\ExportHandler\I\PublicAccess\Repository;

use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Element\ilFactoryInterface as ilExportHandlerPublicAccessRepositoryElementFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\ilHandlerInterface as ilExportHandlerPublicAccessRepositoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Key\ilFactoryInterface as ilExportHandlerPublicAccessRepositoryKeyFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Values\ilFactoryInterface as ilExportHandlerPublicAccessRepositoryValuesFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\Wrapper\ilFactoryInterface as ilExportHandlerPublicAccessRepositoryWrapperFactoryInterface;

interface ilFactoryInterface
{
    public function element(): ilExportHandlerPublicAccessRepositoryElementFactoryInterface;

    public function handler(): ilExportHandlerPublicAccessRepositoryInterface;

    public function key(): ilExportHandlerPublicAccessRepositoryKeyFactoryInterface;

    public function values(): ilExportHandlerPublicAccessRepositoryValuesFactoryInterface;

    public function wrapper(): ilExportHandlerPublicAccessRepositoryWrapperFactoryInterface;
}
