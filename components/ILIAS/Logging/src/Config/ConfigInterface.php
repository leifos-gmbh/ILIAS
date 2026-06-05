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

namespace ILIAS\Logging\Config;

use ILIAS\Logging\ILIASLogLevel;

interface ConfigInterface
{
    public function isLoggingEnabled(): bool;

    // TODO could maybe get rolled into getLogFile?
    public function getLogDirectory(): string;

    public function getLogFile(): string;

    public function getDefaultLevel(): ILIASLogLevel;

    // TODO maybe the root logger could just use the default level?
    public function getLevelForRootLogger(): ILIASLogLevel;

    public function isCacheEnabled(): bool;

    public function getCacheLevel(): ILIASLogLevel;

    // TODO maybe not needed?
    public function isMemoryUsageEnabled(): bool;

    // TODO maybe should be abandoned?
    public function isBrowserLogEnabled(): bool;

    // TODO maybe should be abandoned?
    public function isBrowserLogEnabledForUser(string $login): bool;

    // TODO maybe should be abandoned?
    /**
     * @return string[]
     */
    public function getBrowserLogUserLogins(): array;
}
