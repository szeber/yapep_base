<?php
declare(strict_types=1);

namespace YapepBase\Error\Registry;

use YapepBase\Error\Handler\IErrorHandler;
use YapepBase\ErrorHandler\ITerminatable;
use YapepBase\Exception\Exception;

interface IErrorHandlerRegistry
{
    /**
     * Registers the error handler container as the system error handler.
     */
    public function register(): void;

    /**
     * Unregisters as the system error handler container.
     */
    public function unregister(): void;

    /**
     * Adds an error handler to the container.
     */
    public function addErrorHandler(IErrorHandler $errorHandler): void;

    /**
     * Removes an error handler from the container.
     */
    public function removeErrorHandler(IErrorHandler $errorHandler): void;

    /**
     * Returns the error handlers assigned to the container.
     *
     * @return IErrorHandler[]
     */
    public function getErrorHandlers(): array;

    /**
     * Handles an error.
     *
     * Should not be called manually, only by PHP.
     *
     * @return bool   TRUE if we were able to handle the error, FALSE otherwise.
     */
    public function handleError(int $errorCode, string $message, string $file, int $line): bool;

    /**
     * Handles an unhandled exception
     *
     * Should not be called manually, only by PHP.
     */
    public function handleException(\Exception $exception): void;

    /**
     * Sets the terminator object.
     *
     * @throws Exception
     */
    public function setTerminator(ITerminatable $terminator): void;

    /**
     * Handles script shutdown.
     */
    public function handleShutdown(): void;
}
