<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Syslog;

use YapepBase\Exception\NotImplementedException;
use YapepBase\Exception\SyslogException;

/**
 * This class uses the native PHP syslog calls.
 *
 * This class is not testable.
 *
 * @codeCoverageIgnore
 */
class LegacySyslogConnection extends SyslogConnection
{
    /**
     * Set the path of the log socket. Only works before open()/openlog(). Normally you shouldn't need to use it.
     *
     * @param string $path   Defaults to /dev/log.
     *
     * @return void
     *
     * @throws \YapepBase\Exception\NotImplementedException because PHP's syslog functions do not accept a log socket.
     */
    public function setPath($path = '/dev/log')
    {
        throw new NotImplementedException('The PHP syslog functions can only use the default log socket.');
    }

    /**
     * Opens the log socket. This implementation does nothing.
     *
     * @return \YapepBase\Syslog\LegacySyslogConnection
     *
     * @throws \YapepBase\Exception\SyslogException on error
     */
    public function open()
    {
        return $this;
    }

    /**
     * Closes the log socket.  This implementation does nothing.
     *
     * @return \YapepBase\Syslog\LegacySyslogConnection
     */
    public function close()
    {
        return $this;
    }

    /**
     * Write a log message to the log socket.
     *
     * @param int    $priority   The priority.
     * @param string $message    The message.
     * @param string $ident      Defaults to the ident set via setIdent()
     * @param int    $date       Timestamp for log message. Defaults to now.
     *
     * @return \YapepBase\Syslog\LegacySyslogConnection
     *
     * @throws \YapepBase\Exception\SyslogException on error
     * @throws \YapepBase\Exception\NotImplementedException if the date is set, because this implementation
     *                                                      doesn't support it.
     */
    public function log($priority, $message, $ident = null, $date = null)
    {
        $this->validatePriority($priority);
        $this->validateIdent($ident);
        if (!\is_null($date)) {
            throw new NotImplementedException('PHP\'s internal syslog functions don\'t support passing the date');
        }
        if (!is_string($ident)) {
            $ident = $this->ident;
        }
        if (!openlog($ident, $this->options, $this->facility)) {
            throw new SyslogException('openlog() failed');
        } else {
            if (!syslog($priority, $message)) {
                $e = new SyslogException('syslog() failed');
            }
            if (!closelog()) {
                if (isset($e)) {
                    throw new SyslogException('syslog() and closelog() failed');
                } else {
                    throw new SyslogException('closelog() failed');
                }
            } elseif (isset($e)) {
                throw $e;
            }
        }

        return $this;
    }
}
