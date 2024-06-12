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

namespace ILIAS\MetaData\OERExposer\OAIPMH\Requests;

use ILIAS\MetaData\OAIPMH\Requests\Verb;
use ILIAS\MetaData\OAIPMH\Requests\Argument;
use ILIAS\Data\URI;

class Request implements RequestInterface
{
    protected Verb $verb;
    protected URI $base_url;
    protected array $arguments = [];

    public function __construct(
        URI $base_url,
        Verb $verb
    ) {
        $this->base_url = $base_url;
        $this->verb = $verb;
    }

    public function verb(): Verb
    {
        return $this->verb;
    }

    public function baseURL(): URI
    {
        return $this->base_url;
    }

    public function withArgument(Argument $key, string $value): RequestInterface
    {
        $clone = clone $this;
        $clone->arguments[$key->value] = $value;
        return $clone;
    }

    public function argumentValue(Argument $argument): string
    {
        return $this->arguments[$argument->value] ?? '';
    }

    public function hasArgument(Argument $argument): bool
    {
        return array_key_exists($argument->value, $this->arguments);
    }

    public function argumentKeys(): \Generator
    {
        foreach ($this->arguments as $key => $value) {
            yield Argument::from($key);
        }
    }
}
