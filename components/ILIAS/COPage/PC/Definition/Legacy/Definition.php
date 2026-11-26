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

namespace ILIAS\COPage\PC\Definition\Legacy;

use ILIAS\COPage\PC\Definition\Domain\DomainDefinitionInterface;
use ILIAS\COPage\PC\Definition\Domain\Environment\EnvironmentInterface as DomainEnvironmentInterface;
use ILIAS\COPage\PC\Definition\GUI\GUIDefinitionInterface;
use ILIAS\COPage\PC\Definition\GUI\Environment\EnvironmentInterface as GUIEnvironmentInterface;
use ILIAS\COPage\PC\Definition\DefinitionInterface;
use ilLanguage;

class Definition implements DefinitionInterface
{
    protected GUIDefinitionInterface $gui;
    protected DomainDefinitionInterface $domain;

    public function __construct(
        protected ilLanguage $lng,
        protected string $pc_type,
        protected string $name,
        protected int $oder_nr,
        protected bool $def_enabled,
        protected bool $top_item
    ) {
    }

    public function PCType(): string
    {
        return $this->pc_type;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function orderIndex(): int
    {
        return $this->oder_nr;
    }

    public function GUI(GUIEnvironmentInterface $environment): GUIDefinitionInterface
    {
        return $this->gui = new GUIDefinition(
            $this->lng,
            $this->name,
            $this->pc_type,
            $this->top_item
        );
    }

    public function domain(DomainEnvironmentInterface $environment): DomainDefinitionInterface
    {
        return $this->domain = new DomainDefinition(
            $environment,
            $this->name,
            $this->def_enabled
        );
    }
}
