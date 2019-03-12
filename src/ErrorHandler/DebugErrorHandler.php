<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\ErrorHandler;

use YapepBase\Application;
use YapepBase\Debugger\Item\ErrorItem;

/**
 * ErrorHandler used to log errors to the configured debugger.
 */
class DebugErrorHandler implements IErrorHandler
{
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
    public function handleError($errorLevel, $message, $file, $line, $context, $errorId, array $backTrace = [])
    {
        $debugger = Application::getInstance()->getDiContainer()->getDebugger();

        if ($debugger) {
            $debugItem = new ErrorItem($errorLevel, $message, $file, $line, $context, $errorId);
            $debugger->addItem($debugItem);
        }
    }

    /**
     * Handles an uncaught exception. The exception must extend the \Exception class to be handled.
     *
     * @param \Exception|\Throwable $exception   The exception to handle.
     * @param string                $errorId     The internal ID of the error.
     */
    public function handleException($exception, $errorId)
    {
        $this->handleError(
            ErrorHandlerHelper::E_EXCEPTION,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            ['Exception' => $exception],
            $errorId,
            $exception->getTrace()
        );
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
    public function handleShutdown($errorLevel, $message, $file, $line, $errorId)
    {
        $debugger = Application::getInstance()->getDiContainer()->getDebugger();

        if ($debugger) {
            $debugItem = new ErrorItem($errorLevel, $message, $file, $line, [], $errorId);
            $debugger->addItem($debugItem);

            // In case of Fatal error, the code probably halted, so we have to handle it
            $debugger->handleShutdown();
        }
    }
}
