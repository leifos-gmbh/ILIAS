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

namespace ILIAS\MetaData\Editor\Full\Services\Actions;

use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Editor\Http\LinkFactoryInterface as LinkFactory;
use ILIAS\Data\URI;
use ILIAS\MetaData\Editor\Http\Command;
use ILIAS\MetaData\Editor\Http\Parameter;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Elements\ElementInterface;
use ILIAS\MetaData\Editor\Http\StandardAction;
use ILIAS\MetaData\Editor\Http\AsyncAction;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

class LinkProvider
{
    protected LinkFactory $link_factory;
    protected PathFactory $path_factory;

    public function __construct(
        LinkFactory $link_factory,
        PathFactory $path_factory
    ) {
        $this->link_factory = $link_factory;
        $this->path_factory = $path_factory;
    }

    public function standard(
        PathInterface $base_path,
        ElementInterface $action_element,
        StandardAction $action
    ): URI {
        $action_path = $this->path_factory->toElement($action_element, true);
        return $this->link_factory
            ->standard(Command::ACTION_FULL)
            ->withParameter(Parameter::ACTION, $action->value)
            ->withParameter(Parameter::BASE_PATH, $base_path->toString())
            ->withParameter(Parameter::ACTION_PATH, $action_path->toString())
            ->get();
    }

    public function async(
        PathInterface $base_path,
        ElementInterface $action_element,
        AsyncAction $action
    ): URI {
        $action_path = $this->path_factory->toElement($action_element, true);
        return $this->link_factory
            ->async(Command::ACTION_FULL_ASYNC)
            ->withParameter(Parameter::ASYNC_ACTION, $action->value)
            ->withParameter(Parameter::BASE_PATH, $base_path->toString())
            ->withParameter(Parameter::ACTION_PATH, $action_path->toString())
            ->get();
    }

    /**
     * Also returns token for the action path
     * @return array{0: URLBuilder, 1: URLBuilderToken}
     */
    public function asyncForTable(
        PathInterface $base_path,
        AsyncAction $action
    ): array {
        return $this->link_factory
            ->async(Command::ACTION_FULL_ASYNC)
            ->withParameter(Parameter::ASYNC_ACTION, $action->value)
            ->withParameter(Parameter::BASE_PATH, $base_path->toString())
            ->getAsBuilder(Parameter::ACTION_PATH);
    }
}
