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

namespace ILIAS\Export\ExportHandler\I\Consumer;

use ILIAS\Export\ExportHandler\I\Consumer\Context\ilFactoryInterface as ilExportHandlerConsumerContextFactoryInterface;
use ILIAS\Export\ExportHandler\I\Consumer\ExportOption\ilFactoryInterface as ilExportHandlerConsumerExportOptionFactoryInterface;
use ILIAS\Export\ExportHandler\I\Consumer\File\ilFactoryInterface as ilExportHandlerConsumerFileFactoryInterface;
use ILIAS\Export\ExportHandler\I\Consumer\ilHandlerInterface as ilExportHandlerConsumerInterface;

interface ilFactoryInterface
{
    public function handler(): ilExportHandlerConsumerInterface;

    public function exportOption(): ilExportHandlerConsumerExportOptionFactoryInterface;

    public function context(): ilExportHandlerConsumerContextFactoryInterface;

    public function file(): ilExportHandlerConsumerFileFactoryInterface;
}
