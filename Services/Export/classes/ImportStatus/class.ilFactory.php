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

namespace ImportStatus;

use ImportStatus\I\Content\ilFactoryInterface as ilImportStatusContentFactoryInterface;
use ImportStatus\I\ilFactoryInterface as ilImportStatusFactoryInterface;
use ImportStatus\I\ilHandlerCollectionInterface as ilImportStatusHandlerCollectionInterface;
use ImportStatus\I\ilHandlerInterface as ilImportStatusHandlerInterface;
use ImportStatus\Content\ilFactory as ilImportStatusContentFactory;
use ImportStatus\ilHandlerCollection as ilImportStatusHandlerCollection;
use ImportStatus\ilHandler as ilImportStatusHandler;

class ilFactory implements ilImportStatusFactoryInterface
{
    public function content(): ilImportStatusContentFactoryInterface
    {
        return new ilImportStatusContentFactory();
    }

    public function handler(): ilImportStatusHandlerInterface
    {
        return new ilImportStatusHandler();
    }

    public function handlerCollection(): ilImportStatusHandlerCollectionInterface
    {
        return new ilImportStatusHandlerCollection();
    }
}
