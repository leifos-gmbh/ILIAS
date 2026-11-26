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

use ILIAS\COPage\Resources\ResourcesInterface;
use ILIAS\COPage\Editor\Components\PageComponentModelProvider;
use ILIAS\COPage\PC\Definition\Domain\HandlerInterface;
use ILIAS\COPage\PC\Definition\Domain\DomainDefinitionInterface;
use ilPageContent;
use ILIAS\COPage\PC\Definition\Domain\Environment\EnvironmentInterface;

class DomainDefinition implements DomainDefinitionInterface
{
    protected ilPageContent $content;

    public function __construct(
        protected EnvironmentInterface $environment,
        protected string $name,
        protected bool $def_enabled
    ) {
    }

    public function isEnabledByDefault(): bool
    {
        return $this->def_enabled;
    }

    public function resources(): ResourcesInterface
    {
        return new Resources(
            $this->environment->context()->outputMode(),
            $this->getLegacyContent()
        );
    }

    // TODO better name
    public function handler(): HandlerInterface
    {
        return new DomainHandler(
            $this->environment,
            $this->getLegacyContent()
        );
    }

    public function modelProvider(): ?PageComponentModelProvider
    {
        $pc_class = 'ilPC' . $this->name . 'ModelProvider';
        if (class_exists($pc_class)) {
            return new $pc_class();
        }
        return null;
    }

    protected function getLegacyContent(): ilPageContent
    {
        if (isset($this->content)) {
            return $this->content;
        }
        $class_name = 'ilPC' . $this->name;
        return new $class_name();
    }
}
