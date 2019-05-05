<?php
declare(strict_types=1);

namespace YapepBase\Error\Registry;

use YapepBase\Application;
use YapepBase\Error\Handler\IErrorHandler;
use YapepBase\Error\Helper\ErrorHelper;
use YapepBase\Error\Entity\Error;
use YapepBase\Error\Entity\ExceptionEntity;
use YapepBase\ErrorHandler\ITerminatable;
use YapepBase\Event\Event;
use YapepBase\Exception\Exception;
use YapepBase\Response\IResponse;

/**
 * Registry class holding the registered error handlers.
 */
class ErrorHandlerRegistry implements IErrorHandlerRegistry
{
    /** @var IIdGenerator */
    private $idGenerator;

    /** @var ErrorHelper */
    private $errorHelper;

    /** @var IResponse|null */
    private $response;

    /** @var IErrorHandler[] */
    private $errorHandlers = [];

    /** @var bool */
    private $isRegistered = false;

    /** @var Error|null */
    private $currentlyHandledError;

    /** @var ExceptionEntity|null */
    private $currentlyHandledException;

    /** @var ITerminatable */
    private $terminator;


    public function __construct(IIdGenerator $idGenerator, ErrorHelper $errorHelper, ?IResponse $response = null)
    {
        $this->idGenerator = $idGenerator;
        $this->errorHelper = $errorHelper;
        $this->response    = $response;
    }

    public function __destruct()
    {
        $this->unregister();
    }

    public function register(): void
    {
        if ($this->isRegistered) {
            return;
        }

        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);

        $this->isRegistered = true;
    }

    public function unregister(): void
    {
        if (!$this->isRegistered) {
            return;
        }

        restore_error_handler();
        restore_exception_handler();

        $this->isRegistered = false;
    }

    public function add(IErrorHandler $errorHandler): void
    {
        $this->errorHandlers[] = $errorHandler;
    }

    public function remove(IErrorHandler $errorHandler): void
    {
        $index = array_search($errorHandler, $this->errorHandlers);

        unset($this->errorHandlers[$index]);
    }

    /**
     * @return IErrorHandler[]
     */
    public function getAll(): array
    {
        return $this->errorHandlers;
    }

    public function handleError(int $errorCode, string $message, string $file, int $line): bool
    {
        if (
            !$this->shouldHandleError($errorCode)
            || empty($this->errorHandlers)
        ) {
            return false;
        }

        $backTrace = debug_backtrace();
        // We are the first element, remove it from the trace
        array_shift($backTrace);

        $error = (new Error($errorCode, $message, $file, $line))->setBackTrace($backTrace);
        $this->idGenerator->generateId($error);

        $this->currentlyHandledError = $error;

        foreach ($this->errorHandlers as $errorHandler) {
            $errorHandler->handleError($error);
        }

        $this->currentlyHandledError = null;

        $this->handleIfFatalError($errorCode);

        return true;
    }

    public function handleException(\Exception $exception): void
    {
        if (empty($this->errorHandlers)) {
            // We have no error handlers, trigger an error and let the default error handler handle it
            $this->unregister();
            trigger_error('Unhandled exception: ' . $exception->getMessage(), E_USER_ERROR);

            return;
        }

        $exceptionEntity    = new ExceptionEntity($exception);
        $errorFromException = $exceptionEntity->toError();

        $this->idGenerator->generateId($errorFromException);
        $exceptionEntity->setErrorId($errorFromException->getId());

        $this->currentlyHandledException = $exceptionEntity;

        foreach ($this->errorHandlers as $errorHandler) {
            /** @var IErrorHandler $errorHandler */
            $errorHandler->handleException($exceptionEntity);
        }

        $this->currentlyHandledException = null;
    }

    public function handleShutdown(): void
    {
        $error = $this->errorHelper->getLastError();

        if (
            empty($error)
            || $this->isApplicationFinished()
            || !$this->errorHelper->isFatal($error->getCode())
        ) {
            $this->terminate(false);

            return;
        }

        // We are shutting down because of a fatal error, if any more errors occur, they should be handled by
        // the default error handler.
        $this->unregister();

        if (empty($this->errorHandlers)) {
            $logMessage = 'No error handlers are defined and a fatal error occurred: '
                . $error->getMessage()
                . '. File: ' . $error->getFile()
                . ', line: ' . $error->getLine()
                . '. Type: ' . $error->getCode();

            $this->errorHelper->log($logMessage);
            $this->terminate(true);
            return;
        }

        $this->idGenerator->generateId($error);

        foreach ($this->errorHandlers as $errorHandler) {
            $errorHandler->handleShutdown($error);
        }

        $this->handlePreviouslyUnhandledError();

        $this->terminate(true);
    }

    public function setTerminator(ITerminatable $terminator): void
    {
        if (!empty($this->terminator)) {
            throw new Exception('Trying to set terminator when there is already one set');
        }

        $this->terminator = $terminator;
    }

    private function handleIfFatalError(int $errorCode): void
    {
        if (!$this->errorHelper->isFatal($errorCode)) {
            return;
        }

        $this->unregister();
        $this->sendErrorToResponse();
        $this->errorHelper->exit();
    }

    private function terminate(bool $isFatalError): void
    {
        if (!$this->isApplicationStarted()) {
            $this->sendErrorToResponse();
        }

        if (!empty($this->terminator)) {
            $this->terminator->terminate($isFatalError);
        }

        $this->errorHelper->exit();
    }

    /**
     * Handles previously unhandled errors
     *
     * This can be caused by errors in one of the registered
     * error handlers, or by a php bug, that is triggered by a strict error that is triggered while autoloading.
     * @see https://bugs.php.net/bug.php?id=54054
     */
    private function handlePreviouslyUnhandledError(): void
    {
        if (!empty($this->currentlyHandledError)) {
            $previousError = $this->currentlyHandledError;
        }
        elseif (!empty($this->currentlyHandledException)) {
            $previousError = $this->currentlyHandledException->toError();
        }
        else {
            return;
        }

        $message = 'A fatal error occurred while handling the error with the id: ' . $previousError->getId();

        $errorForShutdown = new Error($previousError->getCode(), $message, $previousError->getFile(), $previousError->getLine());
        $this->idGenerator->generateId($errorForShutdown);

        foreach ($this->errorHandlers as $errorHandler) {
            $errorHandler->handleShutdown($errorForShutdown);
        }

        if (!empty($this->currentlyHandledError)) {
            foreach ($this->errorHandlers as $errorHandler) {
                $errorHandler->handleError($this->currentlyHandledError);
            }

        }
        elseif (!empty($this->currentlyHandledException)) {
            foreach ($this->errorHandlers as $errorHandler) {
                $errorHandler->handleException($this->currentlyHandledException);
            }
        }
    }

    private function shouldHandleError(int $errorCode): bool
    {
        $errorReporting = error_reporting();

        return (bool)($errorCode & $errorReporting);
    }

    private function sendErrorToResponse(): void
    {
        if (empty($this->response)) {
            return;
        }

        $this->response->sendError();
    }

    private function isApplicationFinished(): bool
    {
        return Application
            ::getInstance()
            ->getDiContainer()
            ->getEventHandlerRegistry()
            ->isRaised(Event::TYPE_APPLICATION_AFTER_RUN);
    }

    private function isApplicationStarted(): bool
    {
        return Application
            ::getInstance()
            ->getDiContainer()
            ->getEventHandlerRegistry()
            ->isRaised(Event::TYPE_APPLICATION_BEFORE_RUN);
    }
}
