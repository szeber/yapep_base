<?php
declare(strict_types=1);

namespace YapepBase\Error\Handler;

use YapepBase\Error\Entity\Error;
use YapepBase\Error\Entity\ExceptionEntity;

/**
 * This error handler prints all error messages to std out.
 *
 * Useful for CLI scripts or for development. Do not use for production web applications!
 */
class EchoErrorHandler implements IErrorHandler
{
    public function handleError(Error $error): void
    {
        echo (string)$error . ' (ID: ' . $error->getId() . ')' . PHP_EOL;
    }

    public function handleException(ExceptionEntity $exception): void
    {
        echo (string)$exception . ' (ID: ' . $exception->getErrorId() . ')' . PHP_EOL;
    }

    public function handleShutdown(Error $error): void
    {
        $this->handleError($error);
    }
}
