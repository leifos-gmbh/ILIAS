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

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactoryInterface;
use ILIAS\UI\URLBuilder;

class RequestParser implements RequestParserInterface
{
    use NamespaceHelper;

    protected GlobalHttpState $http;
    protected Refinery $refinery;
    protected PathFactoryInterface $path_factory;

    public function __construct(
        GlobalHttpState $http,
        Refinery $refinery,
        PathFactoryInterface $path_factory
    ) {
        $this->http = $http;
        $this->refinery = $refinery;
        $this->path_factory = $path_factory;
    }

    public function fetchBasePath(): PathInterface
    {
        return $this->fetchPath(Parameter::BASE_PATH, false);
    }

    public function fetchActionPath(): PathInterface
    {
        return $this->fetchPath(Parameter::ACTION_PATH, true);
    }

    public function fetchAction(): StandardAction
    {
        $action_string = $this->fetchActionAsString(Parameter::ACTION);
        $action = StandardAction::tryFrom($action_string);
        if ($action !== null) {
            return $action;
        }
        throw new \ilMDEditorException('No valid action parameter found.');
    }

    public function fetchAsyncAction(): AsyncAction
    {
        $action_string = $this->fetchActionAsString(Parameter::ASYNC_ACTION);
        $action = AsyncAction::tryFrom($action_string);
        if ($action !== null) {
            return $action;
        }
        throw new \ilMDEditorException('No valid action parameter found.');
    }

    public function fetchRequest(
        bool $apply_to_forms
    ): RequestInterface {
        return new Request(
            $request = $this->http->request(),
            $apply_to_forms
        );
    }

    protected function fetchPath(
        Parameter $parameter,
        bool $throw_error
    ): PathInterface {
        $name = $this->buildParameterName($parameter);
        $request_wrapper = $this->http->wrapper()->query();
        if ($request_wrapper->has($name)) {
            $path_string = $request_wrapper->retrieve(
                $name,
                $this->refinery->byTrying([
                    $this->refinery->kindlyTo()->string(),
                    $this->refinery->kindlyTo()->listOf(
                        $this->refinery->kindlyTo()->string()
                    )
                ])
            );
            if (is_array($path_string)) {
                $path_string = $path_string[0];
            }
            return $this->path_factory->fromString(urldecode($path_string));
        }
        if ($throw_error) {
            throw new \ilMDEditorException('Parameter not found.');
        } else {
            return $this->path_factory->custom()->get();
        }
    }

    protected function fetchActionAsString(Parameter $parameter): string
    {
        $name = $this->buildParameterName($parameter);
        $request_wrapper = $this->http->wrapper()->query();
        if ($request_wrapper->has($name)) {
            return $request_wrapper->retrieve(
                $name,
                $this->refinery->kindlyTo()->string()
            );
        }
        throw new \ilMDEditorException('No valid action parameter found.');
    }

    protected function buildParameterName(Parameter $parameter): string
    {
        return implode(URLBuilder::SEPARATOR, self::NAMESPACE) .
            URLBuilder::SEPARATOR . $parameter->value;
    }
}
