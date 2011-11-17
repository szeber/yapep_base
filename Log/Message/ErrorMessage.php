<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Log
 * @subpackage   Message
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Log\Message;

/**
 * PhpErrorMessage class.
 *
 * Formats and stores a PHP error data for logging.
 *
 * @package    YapepBase
 * @subpackage Log
 * @subpackage Message
 */
class ErrorMessage extends MessageAbstract {

    /**
     * Retuns the log tag
     *
     * @return string
     */
    public function getTag() {
        return 'phpErrorLog';
    }

    /**
     * Sets the message data.
     *
     * @param string $errorMessage   The message of the error.
     * @param string $errorType      The textual representation of the error type. {@uses IErrorHandler::E_*}
     * @param string $errorId        The ID of the error.
     * @param int    $priority       The severity of the error {@uses LOG_*}
     */
    public function set($errorMessage, $errorType, $errorId, $priority) {
        $this->message = $errorMessage;
        $this->fields = array($errorType, $errorId);
        $this->priority = $priority;
    }
}