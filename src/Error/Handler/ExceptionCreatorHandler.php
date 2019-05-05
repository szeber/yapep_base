<?php
declare(strict_types=1);

namespace YapepBase\Error\Handler;

use YapepBase\Error\Entity\Error;
use YapepBase\Error\Entity\ExceptionEntity;

/**
 * This error handler converts the handled errors to ErrorException
 */
class ExceptionCreatorHandler implements IErrorHandler
{
    /** @var int */
    private $errorReporting;

    public function __construct()
    {
        $this->errorReporting = error_reporting();
    }

    public function handleError(Error $error): void
    {
        if (
            $this->isErrorSuppressed()
            || !$this->shouldConvert($error->getCode())
        ) {
            return;
        }

        throw new \ErrorException($error->getMessage(), 0, $error->getCode(), $error->getFile(), $error->getLine());
    }

    public function handleException(ExceptionEntity $exceptionEntity): void
    {
        // The method does not have to do anything
    }

    public function handleShutdown(Error $error): void
    {
        // We can't do anything here
    }

    private function isErrorSuppressed(): bool
    {
        return error_reporting() === 0;
    }

    private function shouldConvert(int $errorCode): bool
    {
        return ($this->errorReporting & $errorCode) !== 0;
    }
}
