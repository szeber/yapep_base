<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   ErrorHandler
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\ErrorHandler;
use YapepBase\Config;

use YapepBase\Log\Message\ErrorMessage;

use YapepBase\DependencyInjection\SystemContainer;

use YapepBase\Log\ILogger;

/**
 * Logging error handler class
 *
 * Logs errors to the specified logger
 *
 * @package    YapepBase
 * @subpackage ErrorHandler
 */
class LoggingErrorHandler implements IErrorHandler {

    /**
     *
     * @var YapepBase\Log\ILogger
     */
    protected $logger;

    public function __construct(ILogger $logger) {
        $this->logger = $logger;
    }

    /**
     * Handles a PHP error
     *
     * @param int    $errorLevel   The error code {@uses E_*}
     * @param string $message      The error message.
     * @param string $file         The file where the error occured.
     * @param int    $line         The line in the file where the error occured.
     * @param string $context      The context of the error. (All variables that exist in the scope the error occured)
     * @param string $errorId      The internal ID of the error.
     */
    public function handleError($errorLevel, $message, $file, $line, $context, $errorId) {
        $helper = new ErrorHandlerHelper();
        $errorLevelDescription = $helper->getPhpErrorLevelDescription($errorLevel);

        $errorMessage = '[' . $errorLevelDescription . '(' . $errorLevel . ']: ' . $message . ' on line ' . $line
            . ' in ' . $file;

        $message = SystemContainer::getInstance()->getErrorLogMessage();
        $message->set($errorMessage, $errorLevelDescription, $errorId,
            $helper->getLogPriorityForErrorLevel($errorLevel));

        $this->logger->log($message);
    }

    /**
     * Handles an uncaught exception. The exception must extend the \Exception class to be handled.
     *
     * @param Exception $exception   The exception to handle.
     * @param string $errorId        The internal ID of the error.
     */
    public function handleException(\Exception $exception, $errorId) {
        $errorMessage = '[' . self::EXCEPTION_DESCRIPTION . ']: Unhandled ' . get_class($exception) .': '
            . $exception->getMessage() . '(' . $exception->getCode() .') on line ' . $exception->getLine() . ' in '
            . $exception->getFile();

        $message = SystemContainer::getInstance()->getErrorLogMessage();
        $message->set($errorMessage, self::EXCEPTION_DESCRIPTION, $errorId, LOG_ERR);

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
     */
    function handleShutdown($errorLevel, $message, $file, $line, $errorId) {
        $helper = new ErrorHandlerHelper();
        $errorLevelDescription = $helper->getPhpErrorLevelDescription($errorLevel);

        $errorMessage = '[' . $errorLevelDescription . '(' . $errorLevel . ']: ' . $message . ' on line ' . $line
        . ' in ' . $file;

        $message = SystemContainer::getInstance()->getErrorLogMessage();
        $message->set($errorMessage, $errorLevelDescription, $errorId,
                $helper->getLogPriorityForErrorLevel($errorLevel));

        $this->logger->log($message);
    }


}