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

namespace ILIAS\AdvancedMetaData\Record\File;

use ilDBInterface;
use ILIAS\AdvancedMetaData\Record\File\Handler as ilAMDRecordFile;
use ILIAS\AdvancedMetaData\Record\File\I\FactoryInterface as ilAMDRecordFileFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\I\HandlerInterface as ilAMDRecordFileInterface;
use ILIAS\AdvancedMetaData\Record\File\I\Repository\FactoryInterface as ilAMDRecordFileRepositoryFactoryInterface;
use ILIAS\AdvancedMetaData\Record\File\Repository\Factory as ilAMDRecordFileRepositoryFactory;
use ILIAS\ResourceStorage\Services as ilResourceStorageServices;

class Factory implements ilAMDRecordFileFactoryInterface
{
    protected ilDBInterface $db;
    protected ilResourceStorageServices $irss;

    public function __construct()
    {
        global $DIC;
        $this->db = $DIC->database();
        $this->irss = $DIC->resourceStorage();
    }

    public function handler(): ilAMDRecordFileInterface
    {
        return new ilAMDRecordFile(
            $this,
            $this->irss
        );
    }

    public function repository(): ilAMDRecordFileRepositoryFactoryInterface
    {
        return new ilAMDRecordFileRepositoryFactory(
            $this->db,
            $this->irss
        );
    }
}
