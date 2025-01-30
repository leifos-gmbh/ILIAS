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

namespace ILIAS\MetaData\Editor\Http;

use ILIAS\Data\URI;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\URLBuilder;

class LinkBuilder implements LinkBuilderInterface
{
    use NamespaceHelper;

    /**
     * @var array<string, string> with parameter names as keys
     */
    protected array $parameters = [];
    protected URLBuilder $url_builder;

    public function __construct(
        \ilCtrlInterface $ctrl,
        DataFactory $data_factory,
        Command $command,
        bool $is_async
    ) {
        $link = ILIAS_HTTP_PATH . '/' . $ctrl->getLinkTargetByClass(
            strtolower(\ilMDEditorGUI::class),
            $command->value,
            null,
            $is_async
        );
        $this->url_builder = new URLBuilder($data_factory->uri($link));
    }

    public function withParameter(
        Parameter $parameter,
        string $value
    ): LinkBuilder {
        $clone = clone $this;
        $clone->parameters[$parameter->value] = $value;
        return $clone;
    }

    public function get(): URI
    {
        return $this->getAsBuilder()[0]->buildURI();
    }

    public function getAsBuilder(Parameter ...$additional_parameters): array
    {
        $builder = clone $this->url_builder;
        foreach ($this->parameters as $key => $value) {
            $builder = $builder->acquireParameter(
                self::NAMESPACE,
                $key,
                $value
            )[0];
        }
        return $builder->acquireParameters(
            self::NAMESPACE,
            ...array_map(fn(Parameter $param) => $param->value, $additional_parameters)
        );
    }
}
