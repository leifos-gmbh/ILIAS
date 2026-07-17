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

use ILIAS\Logging\Logger\DefaultLoggerFactoryInterface;
use ILIAS\Logging\Logger\ComponentLoggerFactoryInterface;

/**
 * Logging factory
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilLoggerFactory
{
    protected const ROOT_LOGGER = 'root';

    private static ?ilLoggerFactory $instance = null;

    private DefaultLoggerFactoryInterface $default_factory;
    private ComponentLoggerFactoryInterface $component_factory;
    private ilLoggingSettings $settings;

    /**
     * @var array<string, ilComponentLogger>
     */
    private array $loggers = [];

    protected function __construct()
    {
        global $DIC;

        // TODO set factories from DIC via bootstrapping, also settings
    }

    public static function getInstance(): ilLoggerFactory
    {
        if (!static::$instance instanceof ilLoggerFactory) {
            $settings = ilLoggingDBSettings::getInstance();
            static::$instance = new ilLoggerFactory($settings);
        }
        return static::$instance;
    }


    /**
     * Get component logger
     */
    public static function getLogger(string $a_component_id): ilLogger
    {
        $factory = self::getInstance();
        return $factory->getComponentLogger($a_component_id);
    }

    /**
     * The unique root logger has a fixed error level
     */
    public static function getRootLogger(): ilLogger
    {
        $factory = self::getInstance();
        return $factory->getComponentLogger('root');
    }


    /**
     * Init user specific log options
     */
    public function initUser(string $a_login): void
    {
    }

    /**
     * Check if console handler is available
     */
    protected function isConsoleAvailable(): bool
    {
        if (ilContext::getType() !== ilContext::CONTEXT_WEB) {
            return false;
        }

        if (($this->dic->isDependencyAvailable('ctrl') && $this->dic->ctrl()->isAsynch()) ||
            (
                $this->dic->isDependencyAvailable('http') &&
                strtolower(
                    $this->dic->http()->request()->getServerParams()['HTTP_X_REQUESTED_WITH'] ?? ''
                ) === 'xmlhttprequest'
            )
        ) {
            return false;
        }

        if ($this->dic->isDependencyAvailable('http') &&
            str_contains($this->dic->http()->request()->getServerParams()['HTTP_ACCEPT'] ?? '', 'text/html')) {
            return true;
        }

        if ($this->dic->isDependencyAvailable('http') &&
            str_contains($this->dic->http()->request()->getServerParams()['HTTP_ACCEPT'] ?? '', 'application/json')) {
            return false;
        }

        return true;
    }

    public function getSettings(): ilLoggingSettings
    {
        return $this->settings;
    }

    public function getComponentLogger(string $a_component_id): ilLogger
    {
        if (isset($this->loggers[$a_component_id])) {
            return $this->loggers[$a_component_id];
        }

        if ($a_component_id === 'root') {
            return $this->loggers['root'] = new ilComponentLogger($this->default_factory->getLazy());
        }
        return $this->loggers[$a_component_id] = new ilComponentLogger($this->component_factory->getLazyForComponent($a_component_id));
    }
}
