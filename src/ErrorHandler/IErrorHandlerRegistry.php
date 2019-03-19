<?php
declare(strict_types=1);

namespace YapepBase\ErrorHandler;

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
     * @param int    $errorLevel The error code {@uses E_*}
     * @param string $message    The error message.
     * @param string $file       The file where the error occurred.
     * @param int    $line       The line in the file where the error occurred.
     * @param array  $context    The context of the error. (All variables that exist in the scope the error occurred)
     *
     * @return bool   TRUE if we were able to handle the error, FALSE otherwise.
     */
    public function handleError(int $errorLevel, string $message, string $file, int $line, array $context): bool;

    /**
     * Handles an unhandled exception
     *
     * Should not be called manually, only by PHP.
     *
     * @param \Exception $exception The exception to handle.
     */
    public function handleException(Exception $exception): void;

    /**
     * Sets the terminator object.
     *
     * @throws \YapepBase\Exception\Exception   If trying to add a terminator when a terminator is already set.
     */
    public function setTerminator(ITerminatable $terminator): void;

    /**
     * Handles script shutdown.
     */
    public function handleShutdown(): void;
}
