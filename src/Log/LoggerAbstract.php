<?php
declare(strict_types=1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Log;

use YapepBase\Log\Message\IMessage;

/**
 * Abstract base class usable by loggers.
 *
 * Configuration settings for the loggers should be set in the format:
 * <b>resource.log.&lt;configName&gt;.&lt;optionName&gt;
 */
abstract class LoggerAbstract implements ILogger
{
    /**
     * It really logs the message.
     *
     * @param Message\IMessage $message   The message that should be logged.
     *
     * @return void
     */
    abstract protected function logMessage(IMessage $message);

    /**
     * Logs the message
     *
     * @param \YapepBase\Log\Message\IMessage $message   The message to log.
     *
     * @return void
     */
    public function log(IMessage $message)
    {
        if (!$message->checkIsEmpty()) {
            $this->logMessage($message);
        }
    }
}
