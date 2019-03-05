<?php
declare(strict_types=1);


namespace YapepBase\Log;

use YapepBase\Log\Message\IMessage;

/**
 * Logger interface
 */
interface ILogger
{
    /**
     * Logs the message
     *
     * @param \YapepBase\Log\Message\IMessage $message The message to log.
     *
     * @return void
     */
    public function log(IMessage $message);
}
