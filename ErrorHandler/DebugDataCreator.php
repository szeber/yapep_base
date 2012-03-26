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
use YapepBase\Storage\IStorage;
use YapepBase\ErrorHandler\ErrorHandlerHelper;

/**
 * Error handler that dumps debugging information to a specified storage.
 *
 * @package    YapepBase
 * @subpackage ErrorHandler
 */
class DebugDataCreator implements IErrorHandler  {

    /**
     * The storage instance
     *
     * @var \YapepBase\Storage\IStorage
     */
    protected $storage;

    /**
     * If TRUE, exception traces are not going to be dumped.
     *
     * @var bool
     */
    protected $isTestMode = false;

    /**
     * Constructor.
     *
     * @param \YapepBase\Storage\IStorage $storage      The storage backend to use for the debug data.
     * @param bool                        $isTestMode   If TRUE, exception traces are not going to be dumped.
     */
    public function __construct(IStorage $storage, $isTestMode = false) {
        $this->storage = $storage;
        $this->isTestMode = $isTestMode;
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
     */
    public function handleError($errorLevel, $message, $file, $line, $context, $errorId, array $backTrace = array()) {
        if (false !== $this->storage->get($errorId)) {
            // Only save the debug info, if it's not already saved
            return;
        }

        $helper = new ErrorHandlerHelper();
        $errorLevelDescription = $helper->getPhpErrorLevelDescription($errorLevel);

        $errorMessage = '[' . $errorLevelDescription . '(' . $errorLevel . ')]: ' . $message . ' on line ' . $line
            . ' in ' . $file;

        $this->storage->set($errorId, $this->getDebugData($errorId, $errorMessage, $backTrace, (array)$context));
    }

    /**
     * Handles an uncaught exception. The exception must extend the \Exception class to be handled.
     *
     * @param \Exception $exception   The exception to handle.
     * @param string $errorId        The internal ID of the error.
     */
    public function handleException(\Exception $exception, $errorId) {
        if (false !== $this->storage->get($errorId)) {
            // Only save the debug info, if it's not already saved
            return;
        }

        $errorMessage = '[' . ErrorHandlerHelper::E_EXCEPTION_DESCRIPTION . ']: Unhandled ' . get_class($exception) .': '
            . $exception->getMessage() . '(' . $exception->getCode() .') on line ' . $exception->getLine() . ' in '
            . $exception->getFile();

        $this->storage->set($errorId, $this->getDebugData($errorId, $errorMessage,
            ($this->isTestMode ? array() : $exception->getTrace())));
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
    public function handleShutdown($errorLevel, $message, $file, $line, $errorId) {
        if (false !== $this->storage->get($errorId)) {
            // Only save the debug info, if it's not already saved
            return;
        }

        $helper = new ErrorHandlerHelper();
        $errorLevelDescription = $helper->getPhpErrorLevelDescription($errorLevel);

        $errorMessage = '[' . $errorLevelDescription . '(' . $errorLevel . ')]: ' . $message . ' on line ' . $line
            . ' in ' . $file;

        $this->storage->set($errorId, $this->getDebugData($errorId, $errorMessage));
    }

    /**
     * Returns the debug data as string for the given error.
     *
     * @param string $errorId        The internal ID of the error.
     * @param string $errorMessage   The error message.
     * @param array  $backTrace      The debug backtrace of the error.
     * @param array  $context        The context of the error. (All variables that exist in the scope the error occured)
     *
     * @return string
     */
    protected function getDebugData($errorId, $errorMessage, array $backTrace = array(), array $context = array()) {
        $debugData = $errorId . ' ' . $errorMessage . "\n\n";

        if (!empty($backTrace)) {
            $debugData .= "----- Debug backtrace -----\n\n" . print_r($backTrace, true) . "\n\n";
        }

        if (!empty($context)) {
            $debugData .= "----- Context -----\n\n" . print_r($context, true) . "\n\n";
        }

        if (!empty($_SERVER)) {
            $debugData .= "----- Server -----\n\n" . print_r($_SERVER, true) . "\n\n";
        }

        if (!empty($_GET)) {
            $debugData .= "----- Get -----\n\n" . print_r($_GET, true) . "\n\n";
        }

        if (!empty($_POST)) {
            $debugData .= "----- Post -----\n\n" . print_r($_POST, true) . "\n\n";
        }

        if (!empty($_COOKIE)) {
            $debugData .= "----- Cookie -----\n\n" . print_r($_COOKIE, true) . "\n\n";
        }

        if (!empty($_ENV)) {
            $debugData .= "----- Env -----\n\n" . print_r($_ENV, true) . "\n\n";
        }

        return $debugData;
    }
}