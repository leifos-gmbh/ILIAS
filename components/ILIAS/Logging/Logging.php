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

namespace ILIAS;

class Logging implements Component\Component
{
    public function init(
        array | \ArrayAccess &$define,
        array | \ArrayAccess &$implement,
        array | \ArrayAccess &$use,
        array | \ArrayAccess &$contribute,
        array | \ArrayAccess &$seek,
        array | \ArrayAccess &$provide,
        array | \ArrayAccess &$pull,
        array | \ArrayAccess &$internal,
    ): void {
        $define[] = Logging\Logger\FactoryInterface::class;
        $define[] = Logging\Logger\RootFactoryInterface::class;
        $define[] = Logging\Config\ConfigInterface::class;
        $define[] = Logging\Config\ByComponentInterface::class;

        $internal[Logging\Logger\InternalFactoryInterface::class] = static fn() =>
            new Logging\Logger\InternalFactory(
                $pull[Logging\Config\ConfigInterface::class]
            );
        $internal[Logging\Config\LevelsByComponent\RepositoryInterface::class] = static fn() =>
            new Logging\Config\LevelsByComponent\DBRepository(
                $pull[\ilDBInterface::class] // TODO change to whatever this is now called
            );
        $internal[Logging\Config\Ini\ReaderInterface::class] = static fn() =>
            null; // TODO implement

        $implement[Logging\Logger\FactoryInterface::class] = static fn() =>
            new Logging\Logger\Factory(
                $pull[Logging\Logger\InternalFactoryInterface::class],
                $pull[Logging\Config\ByComponentInterface::class]
            );
        $implement[Logging\Logger\RootFactoryInterface::class] = static fn() =>
            new Logging\Logger\RootFactory(
                $pull[Logging\Logger\InternalFactoryInterface::class],
                $pull[Logging\Config\ConfigInterface::class]
            );
        $implement[Logging\Config\ConfigInterface::class] = static fn() =>
            new Logging\Config\Config(
                $internal[Logging\Config\Ini\ReaderInterface::class]
            );
        $implement[Logging\Config\ByComponentInterface::class] = static fn() =>
            new Logging\Config\ByComponent(
                $internal[Logging\Config\LevelsByComponent\RepositoryInterface::class],
                $pull[Logging\Config\ConfigInterface::class]
            );

        $contribute[Setup\Agent::class] = static fn() =>
            new \ilLoggingSetupAgent(
                $pull[Refinery\Factory::class]
            );
    }
}
