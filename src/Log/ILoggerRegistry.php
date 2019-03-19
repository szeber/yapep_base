<?php
declare(strict_types=1);

namespace YapepBase\Log;

use YapepBase\Exception\Log\LoggerNotFoundException;
use YapepBase\Log\Message\IMessage;

/**
 * Registry class storing the registered loggers.
 */
interface ILoggerRegistry extends ILogger
{
    /**
     * Adds a Logger to the registry.
     */
    public function addLogger(ILogger $logger): void;

    /**
     * Removes a logger from the registry.
     */
    public function removeLogger(ILogger $logger): void;

    /**
     * Returns the loggers assigned to the registry.
     *
     * @return ILogger[]
     */
    public function getLoggers(): array;

    /**
     * Logs the message
     *
     * @throws LoggerNotFoundException   If there are no registered Loggers in the registry.
     */
    public function log(IMessage $message): void;
}
