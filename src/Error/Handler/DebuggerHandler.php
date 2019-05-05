<?php
declare(strict_types=1);

namespace YapepBase\Error\Handler;

use YapepBase\Debug\IDataHandlerRegistry;
use YapepBase\Debug\Item\Error as ErrorDebugItem;
use YapepBase\Error\Entity\Error;
use YapepBase\Error\Entity\ExceptionEntity;

/**
 * ErrorHandler used to log errors to the configured debugger.
 */
class DebuggerHandler implements IErrorHandler
{
    /** @var IDataHandlerRegistry */
    private $debugger;

    public function __construct(IDataHandlerRegistry $debugger)
    {
        $this->debugger = $debugger;
    }

    public function handleError(Error $error): void
    {
        $debugItem = new ErrorDebugItem(
            $error->getCode(),
            $error->getMessage(),
            $error->getFile(),
            $error->getLine(),
            $error->getId()
        );
        $this->debugger->addError($debugItem);
    }

    public function handleException(ExceptionEntity $exceptionEntity): void
    {
        $this->handleError($exceptionEntity->toError());
    }

    public function handleShutdown(Error $error): void
    {
        $this->handleError($error);
    }
}
