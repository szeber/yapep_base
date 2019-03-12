<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Log\Message;

/**
 * Log message interface
 */
interface IMessage
{
    /**
     * Returns the fields set for the log message
     *
     * @return array
     */
    public function getFields();

    /**
     * Returns the log tag
     *
     * @return string
     */
    public function getTag();

    /**
     * Returns the priority for the message
     *
     * @return int   {@uses LOG_*}
     */
    public function getPriority();

    /**
     * Returns the log message
     *
     * @return string
     */
    public function getMessage();

    /**
     * Checks the object is empty or not.
     *
     * @return bool
     */
    public function checkIsEmpty();
}
