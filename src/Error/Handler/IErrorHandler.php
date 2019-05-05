<?php
declare(strict_types=1);

namespace YapepBase\Error\Handler;


use YapepBase\Error\Entity\Error;
use YapepBase\Error\Entity\ExceptionEntity;

interface IErrorHandler
{
    /**
     * Handles PHP errors
     *
     * @throws \ErrorException
     */
    public function handleError(Error $error): void;

    /**
     * Handles exceptions.
     */
    public function handleException(ExceptionEntity $exceptionEntity): void;

    /**
     * Called at script shutdown if the shutdown is because of a fatal error.
     */
    public function handleShutdown(Error $error): void;
}
