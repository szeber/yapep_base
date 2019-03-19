<?php
declare(strict_types=1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Log\Message;

use YapepBase\Config;

/**
 * PhpErrorMessage class.
 *
 * Formats and stores a PHP error data for logging.
 */
class ErrorMessage extends MessageAbstract
{
    /**
     * Retuns the log tag
     *
     * @return string
     */
    public function getTag()
    {
        return 'error';
    }

    /**
     * Sets the message data.
     *
     * @param string $errorMessage   The message of the error.
     * @param string $errorType      The textual representation of the error type. {@uses ErrorHandlerHelper::E_*}
     * @param string $errorId        The ID of the error.
     * @param int    $priority       The severity of the error {@uses LOG_*}
     *
     * @return void
     *
     * @link Config <b>system.application.name</b> key
     */
    public function set($errorMessage, $errorType, $errorId, $priority)
    {
        $this->message = $errorMessage;
        $this->fields  = [
            'error_id' => $errorId,
            'type'     => $errorType,
            'app'      => Config::getInstance()->get('system.application.name', ''),
        ];

        $this->priority = $priority;
    }
}
