<?php
declare(strict_types=1);

namespace YapepBase\Error\Handler;

use YapepBase\Error\Helper\ErrorHelper;
use YapepBase\Error\Entity\Error;
use YapepBase\Error\Entity\ExceptionEntity;
use YapepBase\Log\ILogger;
use YapepBase\Log\Message\ErrorMessage;

/**
 * Logging error handler class.
 *
 * Logs errors to the specified logger.
 */
class LoggingHandler implements IErrorHandler
{
    /** @var ILogger */
    protected $logger;
    /** @var ErrorHelper */
    protected $errorHelper;

    public function __construct(ILogger $logger)
    {
        $this->logger = $logger;
        $this->errorHelper = new ErrorHelper();
    }

    public function handleError(Error $error): void
    {
        $errorCode             = $error->getCode();
        $errorLevelDescription = $this->errorHelper->getDescription($errorCode);

        $message = new ErrorMessage();
        $message->set(
            (string)$error,
            $errorLevelDescription,
            $error->getId(),
            $this->errorHelper->getLogPriorityForErrorCode($errorCode)
        );

        $this->logger->log($message);
    }

    public function handleException(ExceptionEntity $exception): void
    {
        $message = new ErrorMessage();

        $message->set(
            (string)$exception,
            ErrorHelper::E_EXCEPTION_DESCRIPTION,
            $exception->getErrorId(),
            LOG_ERR
        );

        $this->logger->log($message);
    }

    public function handleShutdown(Error $error): void
    {
        $this->handleError($error);
    }
}
