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

namespace ImportHandler\File\XML\Export\Items;

use ilLogger;
use ImportHandler\File\Namespace\ilFactory as ilFileNamespaceFactory;
use ImportHandler\File\Path\ilFactory as ilFilePathFactory;
use ImportHandler\File\XML\Node\Info\Attribute\ilFactory as ilXMLNodeInfoAttributeFactory;
use ImportHandler\File\XSD\ilFactory as ilXSDFileFactory;
use ImportHandler\I\File\XML\Export\Items\ilFactoryInterface as ilItemsXMLExportFileFactoryInterface;
use ImportHandler\I\File\XML\Export\Items\ilHandlerInterface as ilItemsXMLExportFileHandlerInterface;
use ImportHandler\File\XML\Export\Items\ilHandler as ilItemsXMLExportFileHandler;
use ImportHandler\File\Validation\Set\ilFactory as ilFileValidationSetFactory;
use ImportHandler\Parser\ilFactory as ilParserFactory;
use ImportStatus\ilFactory as ilImportStatusFactory;
use Schema\ilXmlSchemaFactory;

class ilFactory implements ilItemsXMLExportFileFactoryInterface
{
    protected ilLogger $logger;

    public function __construct(
        ilLogger $logger
    ) {
        $this->logger = $logger;
    }

    public function handler(): ilItemsXMLExportFileHandlerInterface
    {
        return new ilItemsXMLExportFileHandler(
            new ilFileNamespaceFactory(),
            new ilImportStatusFactory(),
            new ilXmlSchemaFactory(),
            new ilParserFactory($this->logger),
            new ilXSDFileFactory(),
            new ilFilePathFactory($this->logger),
            $this->logger,
            new ilXMLNodeInfoAttributeFactory($this->logger),
            new ilFileValidationSetFactory()
        );
    }
}
