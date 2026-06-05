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
use ILIAS\Logging\Config\Ini\ReaderInterface;

// TODO caching?
class Config implements ConfigInterface
{
    public function __construct(
        protected ReaderInterface $reader
    ) {
    }

    public function isLoggingEnabled(): bool
    {
        return (bool) $this->reader->isLoggingEnabled();
    }

    // TODO could maybe get rolled into getLogFile?
    public function getLogDirectory(): string
    {
        return $this->reader->getLogDirectory();
    }

    public function getLogFile(): string
    {
        return $this->reader->getLogFile();
    }

    public function getDefaultLevel(): ILIASLogLevel
    {
        return $this->logLevelFromString($this->reader->getDefaultLevel());
    }

    // TODO maybe the root logger could just use the default level?
    public function getLevelForRootLogger(): ILIASLogLevel
    {
        return $this->logLevelFromString($this->reader->getLevelForRootLogger());
    }

    public function isCacheEnabled(): bool
    {
        return (bool) $this->reader->isCacheEnabled();
    }

    public function getCacheLevel(): ILIASLogLevel
    {
        return $this->logLevelFromString($this->reader->getCacheLevel());
    }

    // TODO maybe not needed?
    public function isMemoryUsageEnabled(): bool
    {
        return (bool) $this->reader->isMemoryUsageEnabled();
    }

    // TODO maybe should be abandoned?
    public function isBrowserLogEnabled(): bool
    {
        return (bool) $this->reader->isBrowserLogEnabled();
    }

    // TODO maybe should be abandoned?
    public function isBrowserLogEnabledForUser(string $login): bool
    {
        return in_array($login, $this->getBrowserLogUserLogins());
    }

    // TODO maybe should be abandoned?
    /**
     * @return string[]
     */
    public function getBrowserLogUserLogins(): array
    {
        $logins = [];
        foreach (explode(',', $this->reader->getBrowserLogUserLogins()) as $raw_login) {
            $logins[] = trim($raw_login);
        }
        return $logins;
    }

    protected function logLevelFromString(string $raw_level): ILIASLogLevel
    {
        return ILIASLogLevel::tryFromString($raw_level) ??
            ILIASLogLevel::tryFrom((int) $raw_level) ??
            ILIASLogLevel::INFO;
    }
}
