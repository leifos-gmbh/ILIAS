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

namespace ImportHandler\I\File\XML\Reader;

use ImportHandler\File\Path\ilHandler as ilParserPathHandlerInterface;
use ImportHandler\I\File\XML\ilHandlerInterface as ilXMLFileHandlerInterface;
use ImportHandler\I\File\XSD\ilHandlerInterface as ilXSDFileHandlerInterface;
use ImportStatus\I\ilHandlerCollectionInterface as ilImportStatusHandlerCollectionInterface;

interface ilHandlerInterface
{
    public function withXSDFileHandler(ilXSDFileHandlerInterface $xsd_file): ilHandlerInterface;

    public function withXMLFileHandler(ilXMLFileHandlerInterface $xml_file): ilHandlerInterface;

    public function withTargetElement(string $element_name): ilHandlerInterface;

    public function validate(): ilImportStatusHandlerCollectionInterface;
}
