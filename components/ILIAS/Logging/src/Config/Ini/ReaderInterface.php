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

namespace ILIAS\Logging\Config\Ini;

interface ReaderInterface
{
    public function isLoggingEnabled(): string;

    // TODO could maybe get rolled into getLogFile?
    public function getLogDirectory(): string;

    public function getLogFile(): string;

    public function getDefaultLevel(): string;

    // TODO maybe the root logger could just use the default level?
    public function getLevelForRootLogger(): string;

    public function isCacheEnabled(): bool;

    public function getCacheLevel(): string;

    // TODO maybe not needed?
    public function isMemoryUsageEnabled(): string;

    // TODO maybe should be abandoned?
    public function isBrowserLogEnabled(): string;

    // TODO maybe should be abandoned?
    public function getBrowserLogUserLogins(): string;

    // TODO maybe move to Init/Error?
    public function getErrorDirectory(): string;

    // TODO maybe move to Init/Error?
    public function getErrorRecipientLogin(): string;
}
