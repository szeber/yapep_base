<?php
declare(strict_types = 1);

namespace YapepBase\Log;

use YapepBase\Exception\Log\LoggerNotFoundException;
use YapepBase\Log\Message\IMessage;

/**
 * Registry class storing the registered loggers.
 */
class LoggerRegistry implements ILoggerRegistry
{
    /**
     * @var ILogger[]
     */
    protected $loggers = [];

    public function addLogger(ILogger $logger): void
    {
        $this->loggers[] = $logger;
    }

    public function removeLogger(ILogger $logger): void
    {
        $index = array_search($logger, $this->loggers);

        unset($this->loggers[$index]);
    }

    public function getLoggers(): array
    {
        return $this->loggers;
    }

    public function log(IMessage $message): void
    {
        if ($message->checkIsEmpty()) {
            return;
        }

        if (empty($this->loggers)) {
            throw new LoggerNotFoundException('There are no registered Loggers!');
        }

        foreach ($this->loggers as $logger) {
            /** @var ILogger $logger */
            $logger->log($message);
        }
    }
}
