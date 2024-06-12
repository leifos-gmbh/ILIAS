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

interface RequestInterface
{
    public function baseURL(): URI;

    public function verb(): Verb;

    public function withArgument(Argument $key, string $value): RequestInterface;

    public function argumentValue(Argument $argument): string;

    public function hasArgument(Argument $argument): bool;

    /**
     * @return Argument[]
     */
    public function argumentKeys(): \Generator;
}
