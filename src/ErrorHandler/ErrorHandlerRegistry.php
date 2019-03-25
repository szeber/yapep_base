<?php
declare(strict_types=1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\ErrorHandler;

use YapepBase\Application;
use YapepBase\Config;
use YapepBase\Exception\Exception;

/**
 * Registry class holding the registered error handlers.
 */
class ErrorHandlerRegistry implements IErrorHandlerRegistry
{
    /** The default timeout for the error ID in seconds. */
    const ERROR_HANDLING_DEFAULT_ID_TIMEOUT = 600;   // 10 minutes

    /** Error type. */
    const TYPE_ERROR = 'error';

    /** Shutdown type. */
    const TYPE_SHUTDOWN = 'shutdown';

    /** Exception type. */
    const TYPE_EXCEPTION = 'exception';

    /**
     * Array containing the assigned error handlers.
     *
     * @var array
     */
    protected $errorHandlers = [];

    /**
     * Set to TRUE if we are registered as the system error handler.
     *
     * @var bool
     */
    protected $isRegistered = false;

    /**
     * Stores the data of the error that's being handled at the moment.
     *
     * @var array
     */
    protected $currentlyHandledError = [];

    /**
     * The terminator instance.
     *
     * @var ITerminatable
     */
    protected $terminator;

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->unregister();
    }

    public function register(): void
    {
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
        $this->isRegistered = true;
    }

    public function unregister(): void
    {
        if ($this->isRegistered) {
            restore_error_handler();
            restore_exception_handler();
            $this->isRegistered = false;
        }
    }

    public function addErrorHandler(IErrorHandler $errorHandler): void
    {
        $this->errorHandlers[] = $errorHandler;
    }

    public function removeErrorHandler(IErrorHandler $errorHandler): void
    {
        $index = array_search($errorHandler, $this->errorHandlers);

        unset($this->errorHandlers[$index]);
    }

    public function getErrorHandlers(): array
    {
        return $this->errorHandlers;
    }

    public function handleError(int $errorLevel, string $message, string $file, int $line, array $context): bool
    {
        $errorReporting = error_reporting();

        if (!($errorLevel & $errorReporting)) {
            // The error should not be reported
            return false;
        }

        if (empty($this->errorHandlers)) {
            // We have no error handlers, let the standard PHP error handler handle it
            return false;
        }

        $errorId = $this->generateErrorId($message, $file, $line);

        $backTrace = debug_backtrace();
        // We are the first element, remove it from the trace
        array_shift($backTrace);

        $this->currentlyHandledError = [
            'type'    => self::TYPE_ERROR,
            'level'   => $errorLevel,
            'message' => $message,
            'file'    => $file,
            'line'    => $line,
            'context' => $context,
            'trace'   => $backTrace,
            'errorId' => $errorId,
        ];

        foreach ($this->errorHandlers as $errorHandler) {
            /** @var IErrorHandler $errorHandler */
            $errorHandler->handleError($errorLevel, $message, $file, $line, $context, $errorId, $backTrace);
        }

        $this->currentlyHandledError = [];

        // @codeCoverageIgnoreStart
        if ($this->isErrorFatal($errorLevel)) {
            // We encountered a fatal error, so we output an error and exit
            $this->unregister();
            Application::getInstance()
                       ->outputError();
            exit;
        }
        // @codeCoverageIgnoreEnd

        return true;
    }

    public function handleException(Exception $exception): void
    {
        if (!($exception instanceof \Exception) && !($exception instanceof \Error)) {
            // The error handlers can only handle exceptions that are descendants of the Exception built in class
            trigger_error('Unable to handle exception of type: ' . get_class($exception), E_USER_ERROR);

            return;
        }

        if (empty($this->errorHandlers)) {
            // We have no error handlers, trigger an error and let the default error handler handle it
            $this->unregister();
            trigger_error('Unhandled exception: ' . $exception->getMessage(), E_USER_ERROR);

            return;
        }
        $errorId = $this->generateErrorId($exception->getMessage(), $exception->getFile(), $exception->getLine());

        $this->currentlyHandledError = [
            'type'      => self::TYPE_ERROR,
            'exception' => $exception,
            'errorId'   => $errorId,
        ];

        foreach ($this->errorHandlers as $errorHandler) {
            /** @var IErrorHandler $errorHandler */
            $errorHandler->handleException($exception, $errorId);
        }

        $this->currentlyHandledError = [];
    }

    public function setTerminator(ITerminatable $terminator): void
    {
        if (!empty($this->terminator)) {
            throw new Exception('Trying to set terminator when there is already one set');
        }

        $this->terminator = $terminator;
    }

    /**
     * Run when the registered error handler terminates the current execution.
     *
     * @param bool $isFatalError TRUE if the termination is because of a fatal error.
     *
     * @return void
     */
    protected function terminate($isFatalError)
    {
        if (php_sapi_name() != 'cli' && !Application::getInstance()->isStarted()) {
            header('HTTP/1.1 500');
        }

        if (!empty($this->terminator)) {
            $this->terminator->terminate($isFatalError);
        }

        exit;
    }

    public function handleShutdown(): void
    {
        $error = error_get_last();

        if (!$error || !$this->isRegistered || !$this->isErrorFatal($error['type'])) {
            // Normal shutdown or we are not the system handler
            if ($this->isRegistered) {
                // We are only running the termination function if we are the registered error handler.
                $this->terminate(false);
            }

            return;
        }

        // We are shutting down because of a fatal error, if any more errors occur, they should be handled by
        // the default error handler.
        $this->unregister();

        // Shutdown because of a fatal error
        if (empty($this->errorHandlers)) {
            // We have no error handlers defined, sendContent the fatal error to the SAPI's logger.
            $errorMessage = 'No errorhandlers are defined and a fatal error occured: '
                . $error['message']
                . '. File: ' . $error['file']
                . ', line: ' . $error['line']
                . '. Type: ' . $error['type'];

            error_log($errorMessage, 4);
            $this->terminate(true);

            return;
        }

        $errorId = $this->generateErrorId($error['message'], $error['file'], $error['line']);

        foreach ($this->errorHandlers as $errorHandler) {
            /** @var IErrorHandler $errorHandler */
            $errorHandler->handleShutdown($error['type'], $error['message'], $error['file'], $error['line'], $errorId);
        }

        // If we have a previously unhandled error, handle it. This can be caused by errors in one of the registered
        // error handlers, or by a php bug, that is triggered by a strict error that is triggered while autoloading.
        // {@see https://bugs.php.net/bug.php?id=54054}
        if (!empty($this->currentlyHandledError)) {
            $message = 'A fatal error occured while handling the error with the id: ' . $this->currentlyHandledError['errorId'] . '. Fatal error ID: ' . $errorId;

            // If we have a previously unhandled error, handle it. This can be caused by errors in one of the registered
            foreach ($this->errorHandlers as $errorHandler) {
                $errorHandler->handleShutdown(
                    $error['type'],
                    $message,
                    $error['file'],
                    $error['line'],
                    $this->generateErrorId($message, $error['file'], $error['line'])
                );
            }

            switch ($this->currentlyHandledError['type']) {
                case self::TYPE_ERROR:
                    foreach ($this->errorHandlers as $errorHandler) {
                        $errorHandler->handleError(
                            $this->currentlyHandledError['level'],
                            $this->currentlyHandledError['message'],
                            $this->currentlyHandledError['file'],
                            $this->currentlyHandledError['line'],
                            $this->currentlyHandledError['context'],
                            $this->currentlyHandledError['errorId'],
                            $this->currentlyHandledError['trace']
                        );
                    }
                    break;

                case self::TYPE_EXCEPTION:
                    foreach ($this->errorHandlers as $errorHandler) {
                        $errorHandler->handleException(
                            $this->currentlyHandledError['exception'],
                            $this->currentlyHandledError['errorId']
                        );
                    }
                    break;
            }
        }
        $this->terminate(true);
    }

    /**
     * Returns an error ID based on the message, file, line, hostname and current time.
     *
     * Generates the same error ID if the same error occurs within the timeframe set in the
     * 'system.errorHandling.defaultIdTimeout' configuration option. If that option is not set, it will default to the
     * value of the self::ERROR_HANDLING_DEFAULT_ID_TIMEOUT constant. The value should be set as seconds.
     *
     * @param string $message The message of the error.
     * @param string $file    The file where the error occured.
     * @param int    $line    The line where the error occured.
     *
     * @return string
     */
    protected function generateErrorId($message, $file, $line)
    {
        $idTimeout = Config::getInstance()
                           ->get('system.errorHandling.defaultIdTimeout', self::ERROR_HANDLING_DEFAULT_ID_TIMEOUT);

        if (0 == $idTimeout) {
            return md5($message . $file . (string)$line . php_uname('n'));
        } elseif ($idTimeout < 0) {
            return md5($message . $file . (string)$line . php_uname('n') . uniqid(''));
        } else {
            // @codeCoverageIgnoreStart
            return md5($message . $file . (string)$line . php_uname('n') . floor(time() / $idTimeout));
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Returns if the error should be considered fatal by the error level.
     *
     * @param int $errorLevel The error level to check.
     *
     * @return bool
     *
     * @codeCoverageIgnore
     */
    protected function isErrorFatal($errorLevel)
    {
        switch ($errorLevel) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_RECOVERABLE_ERROR:
                return true;
                break;
        }

        return false;
    }
}
