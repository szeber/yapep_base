<?php
declare(strict_types=1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\ErrorHandler;

use YapepBase\Application;
use YapepBase\Log\ILogger;

/**
 * Logging error handler class.
 *
 * Logs errors to the specified logger.
 */
class LoggingErrorHandler implements IErrorHandler
{
    /**
     * The logger to use
     *
     * @var \YapepBase\Log\ILogger
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param \YapepBase\Log\ILogger $logger   The logger to use.
     */
    public function __construct(ILogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Handles a PHP error
     *
     * @param int    $errorLevel   The error code {@uses E_*}
     * @param string $message      The error message.
     * @param string $file         The file where the error occured.
     * @param int    $line         The line in the file where the error occured.
     * @param array  $context      The context of the error. (All variables that exist in the scope the error occured)
     * @param string $errorId      The internal ID of the error.
     * @param array  $backTrace    The debug backtrace of the error.
     *
     * @return void
     */
    public function handleError($errorLevel, $message, $file, $line, $context, $errorId, array $backTrace = [])
    {
        $helper                = new ErrorHandlerHelper();
        $errorLevelDescription = $helper->getPhpErrorLevelDescription($errorLevel);

        $errorMessage = '[' . $errorLevelDescription . '(' . $errorLevel . ')]: ' . $message . ' on line ' . $line
            . ' in ' . $file;

        $message = Application::getInstance()->getDiContainer()->getErrorLogMessage();
        $message->set(
            $errorMessage,
            $errorLevelDescription,
            $errorId,
            $helper->getLogPriorityForErrorLevel($errorLevel)
        );

        $this->logger->log($message);
    }

    /**
     * Handles an uncaught exception. The exception must extend the \Exception class to be handled.
     *
     * @param \Exception|\Throwable $exception   The exception to handle.
     * @param string                $errorId     The internal ID of the error.
     *
     * @return void
     */
    public function handleException($exception, $errorId)
    {
        $errorMessage = '[' . ErrorHandlerHelper::E_EXCEPTION_DESCRIPTION . ']: Unhandled ' . get_class($exception)
            . ': ' . $exception->getMessage() . '(' . $exception->getCode() . ') on line ' . $exception->getLine()
            . ' in ' . $exception->getFile();

        $message = Application::getInstance()->getDiContainer()->getErrorLogMessage();
        $message->set($errorMessage, ErrorHandlerHelper::E_EXCEPTION_DESCRIPTION, $errorId, LOG_ERR);

        $this->logger->log($message);
    }

    /**
     * Called at script shutdown if the shutdown is because of a fatal error.
     *
     * @param int    $errorLevel   The error code {@uses E_*}
     * @param string $message      The error message.
     * @param string $file         The file where the error occured.
     * @param int    $line         The line in the file where the error occured.
     * @param string $errorId      The internal ID of the error.
     *
     * @return void
     */
    public function handleShutdown($errorLevel, $message, $file, $line, $errorId)
    {
        $helper                = new ErrorHandlerHelper();
        $errorLevelDescription = $helper->getPhpErrorLevelDescription($errorLevel);

        $errorMessage = '[' . $errorLevelDescription . '(' . $errorLevel . ')]: ' . $message . ' on line ' . $line
        . ' in ' . $file;
        $message = Application::getInstance()->getDiContainer()->getErrorLogMessage();
        $message->set(
            $errorMessage,
            $errorLevelDescription,
            $errorId,
            $helper->getLogPriorityForErrorLevel($errorLevel)
        );
        $this->logger->log($message);
    }
}
