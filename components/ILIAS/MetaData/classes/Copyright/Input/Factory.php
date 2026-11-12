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

namespace ILIAS\MetaData\Copyright\Input;

use ILIAS\MetaData\Copyright\RepositoryInterface;
use ILIAS\MetaData\Copyright\Identifiers\HandlerInterface;
use ILIAS\MetaData\Presentation\UtilitiesInterface as Presentation;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Refinery\Factory as Refinery;

class Factory implements FactoryInterface
{
    public function __construct(
        protected RepositoryInterface $repo,
        protected HandlerInterface $identifier_handler,
        protected Presentation $presentation,
        protected UIFactory $ui_factory,
        protected Refinery $refinery
    ) {
    }

    public function buildInput(): BuilderInterface
    {
        return new Builder(
            $this->repo,
            $this->identifier_handler,
            $this->presentation,
            $this->ui_factory,
            $this->refinery
        );
    }
}
