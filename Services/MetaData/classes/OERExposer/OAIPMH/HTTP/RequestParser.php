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

namespace ILIAS\MetaData\OERExposer\OAIPMH\HTTP;

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\MetaData\OAIPMH\Requests\Argument;

class RequestParser implements RequestParserInterface
{
    protected GlobalHttpState $http;
    protected Refinery $refinery;

    public function __construct(
        GlobalHttpState $http,
        Refinery $refinery
    ) {
        $this->http = $http;
        $this->refinery = $refinery;
    }

    public function hasArgument(Argument $argument): bool
    {
        return $this->http->wrapper()->query()->has($argument->value) ||
            $this->http->wrapper()->post()->has($argument->value);
    }

    public function retrieveArgument(Argument $argument): string
    {
        if ($this->http->wrapper()->query()->has($argument->value)) {
            return $this->http->wrapper()->query()->retrieve(
                $argument->value,
                $this->refinery->kindlyTo()->string()
            );
        }
        if ($this->http->wrapper()->post()->has($argument->value)) {
            return $this->http->wrapper()->post()->retrieve(
                $argument->value,
                $this->refinery->kindlyTo()->string()
            );
        }
        return '';
    }
}
