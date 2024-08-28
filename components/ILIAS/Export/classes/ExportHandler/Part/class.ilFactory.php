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

namespace ILIAS\Export\ExportHandler\Part;

use ILIAS\Export\ExportHandler\I\ilFactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\Part\Component\ilFactoryInterface as ilExportHanlderPartComponentFactoryInterface;
use ILIAS\Export\ExportHandler\I\Part\ContainerManifest\ilFactoryInterface as ilExportHanlderPartContainerManifestFactoryInterface;
use ILIAS\Export\ExportHandler\I\Part\ilFactoryInterface as ilExportHandlerPartFactoryInterface;
use ILIAS\Export\ExportHandler\I\Part\Manifest\ilFactoryInterface as ilExportHanlderPartManifestFactoryInterface;
use ILIAS\Export\ExportHandler\Part\Component\ilFactory as ilExportHanlderPartComponentFactory;
use ILIAS\Export\ExportHandler\Part\ContainerManifest\ilFactory as ilExportHanlderPartContainerManifestFactory;
use ILIAS\Export\ExportHandler\Part\Manifest\ilFactory as ilExportHanlderPartManifestFactory;

class ilFactory implements ilExportHandlerPartFactoryInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;

    public function __construct(ilExportHandlerFactoryInterface $export_handler)
    {
        $this->export_handler = $export_handler;
    }

    public function manifest(): ilExportHanlderPartManifestFactoryInterface
    {
        return new ilExportHanlderPartManifestFactory($this->export_handler);
    }

    public function component(): ilExportHanlderPartComponentFactoryInterface
    {
        return new ilExportHanlderPartComponentFactory($this->export_handler);
    }

    public function container(): ilExportHanlderPartContainerManifestFactoryInterface
    {
        return new ilExportHanlderPartContainerManifestFactory($this->export_handler);
    }
}
