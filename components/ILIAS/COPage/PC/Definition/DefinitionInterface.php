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

namespace ILIAS\COPage\PC\Definition;

use ILIAS\COPage\PC\Definition\Domain\DomainDefinitionInterface;
use ILIAS\COPage\PC\Definition\Domain\Environment\EnvironmentInterface as DomainEnvironmentInterface;
use ILIAS\COPage\PC\Definition\GUI\GUIDefinitionInterface;
use ILIAS\COPage\PC\Definition\GUI\Environment\EnvironmentInterface as GUIEnvironmentInterface;

interface DefinitionInterface
{
    public function PCType(): string;

    // TODO maybe type is sufficient? Probably both needed, if nothing else for backward compatability
    public function name(): string;

    public function orderIndex(): int;

    public function GUI(GUIEnvironmentInterface $environment): GUIDefinitionInterface;

    public function domain(DomainEnvironmentInterface $environment): DomainDefinitionInterface;

    // TODO maybe rendering as third layer
}
