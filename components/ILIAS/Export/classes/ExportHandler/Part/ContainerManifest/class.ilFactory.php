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

namespace ILIAS\Export\ExportHandler\Part\ContainerManifest;

use ILIAS\Export\ExportHandler\I\ilFactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Part\ContainerManifest\ilFactoryInterface as ilExportHandlerPartContainerManifestFactoryInterface;
use ILIAS\Export\ExportHandler\I\Part\ContainerManifest\ilHandlerInterface as ilExportHandlerPartContainerManifestInterface;
use ILIAS\Export\ExportHandler\Part\ContainerManifest\ilHandler as ilExportHandlerPartContainerManifest;

class ilFactory implements ilExportHandlerPartContainerManifestFactoryInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;

    public function __construct(ilExportHandlerFactoryInterface $export_handler)
    {
        $this->export_handler = $export_handler;
    }

    public function handler(): ilExportHandlerPartContainerManifestInterface
    {
        return new ilExportHandlerPartContainerManifest();
    }
}
