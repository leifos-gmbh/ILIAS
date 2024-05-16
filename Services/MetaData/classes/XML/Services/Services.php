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

namespace ILIAS\MetaData\XML\Services;

use ILIAS\MetaData\XML\Writer\Standard as StandardWriter;
use ILIAS\MetaData\XML\Dictionary\LOMDictionaryInitiator;
use ILIAS\MetaData\XML\Dictionary\TagFactory;
use ILIAS\MetaData\Paths\Services\Services as PathServices;
use ILIAS\MetaData\Structure\Services\Services as StructureServices;
use ILIAS\MetaData\XML\Copyright\CopyrightHandler;

class Services
{
    protected StandardWriter $standard_writer;

    protected PathServices $path_services;
    protected StructureServices $structure_services;

    public function __construct(
        PathServices $path_services,
        StructureServices $structure_services
    ) {
        $this->path_services = $path_services;
        $this->structure_services = $structure_services;
    }

    public function standardWriter(): StandardWriter
    {
        if (isset($this->standard_writer)) {
            return $this->standard_writer;
        }
        $dictionary = (new LOMDictionaryInitiator(
            new TagFactory(),
            $this->path_services->pathFactory(),
            $this->structure_services->structure()
        ))->get();
        return $this->standard_writer = new StandardWriter(
            $dictionary,
            new CopyrightHandler()
        );
    }
}
