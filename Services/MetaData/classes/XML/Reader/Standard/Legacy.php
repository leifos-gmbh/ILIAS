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

namespace ILIAS\MetaData\XML\Reader\Standard;

use ILIAS\MetaData\Elements\Markers\MarkerFactoryInterface;
use ILIAS\MetaData\Manipulator\ScaffoldProvider\ScaffoldProviderInterface;
use ILIAS\MetaData\XML\Copyright\CopyrightHandlerInterface;
use ILIAS\MetaData\XML\Version;
use ILIAS\MetaData\Elements\SetInterface;
use ILIAS\MetaData\XML\Reader\ReaderInterface;

class Legacy implements ReaderInterface
{
    protected MarkerFactoryInterface $marker_factory;
    protected ScaffoldProviderInterface $scaffold_provider;
    protected CopyrightHandlerInterface $copyright_handler;

    public function __construct(
        MarkerFactoryInterface $marker_factory,
        ScaffoldProviderInterface $scaffold_provider,
        CopyrightHandlerInterface $copyright_handler
    ) {
        $this->marker_factory = $marker_factory;
        $this->scaffold_provider = $scaffold_provider;
        $this->copyright_handler = $copyright_handler;
    }

    public function read(
        \SimpleXMLElement $xml,
        Version $version
    ): SetInterface {
        $set = $this->scaffold_provider->set();
        $root_element = $set->getRoot();

        return $set;
    }
}
