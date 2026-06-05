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

namespace ILIAS\Logging\Logger;

use ILIAS\Logging\Config\ConfigInterface;
use ILIAS\Logging\Logger\Extensions\ILIASTraceProcessor;
use ILIAS\Logging\Logger\Extensions\ILIASLineFormatter;
use ILIAS\Logging\ILIASLogLevel;
use Monolog\Logger as MonologLogger;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\Handler;
use Monolog\Processor\PsrLogMessageProcessor;

class InternalFactory implements InternalFactoryInterface
{
    /**
     * @var array<string, Logger>
     */
    protected array $loggers = [];

    public function __construct(
        protected ConfigInterface $config
    ) {
    }

    public function get(string $component_or_root_id, ILIASLogLevel $level): LoggerInterface
    {
        if (isset($this->loggers[$component_or_root_id])) {
            return $this->loggers[$component_or_root_id];
        }

        $logger = new MonologLogger($component_or_root_id);

        if (!$this->config->isLoggingEnabled()) {
            $logger->pushHandler(new NullHandler());
            return $this->loggers[$component_or_root_id] = new Logger($logger);
        }

        $handler = $this->buildStandardHandler($level);
        $logger->pushHandler($handler);
        // TODO browser handler?

        $logger->pushProcessor(function ($record) {
            $record['extra']['suid'] = substr(session_id(), 0, 5);
            return $record;
        }); // suid log
        $logger->pushProcessor(new ILIASTraceProcessor(ILIASLogLevel::DEBUG)); // append trace
        $logger->pushProcessor(new PsrLogMessageProcessor()); // Interpolate context variables.

        return $this->loggers[$component_or_root_id] = new Logger($logger);
    }

    protected function buildStandardHandler(ILIASLogLevel $level): Handler
    {
        $stream_handler = new StreamHandler(
            $this->config->getLogDirectory() . '/' . $this->config->getLogFile(),
            $level->value,
            true
        );

        $line_formatter = new ILIASLineFormatter();
        $stream_handler->setFormatter($line_formatter);

        if (!$this->config->isCacheEnabled()) {
            return $stream_handler;
        }

        // add new finger crossed handler
        $fingers_crossed_handler = new FingersCrossedHandler(
            $stream_handler,
            new ErrorLevelActivationStrategy($this->config->getCacheLevel()->value),
            1000
        );
        return $fingers_crossed_handler;
    }
}
