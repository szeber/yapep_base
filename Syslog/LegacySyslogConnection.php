<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Syslog
 * @author       Janos Pasztor <j.pasztor@ixolit.com>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Syslog;

/**
 * This class uses the native PHP syslog calls.
 * This class is not testable.
 * @codeCoverageIgnore
 */
class LegacySyslogConnection extends SyslogConnection {
    /**
     * Set the path of the log socket. Only works before open()/openlog(). Normally you shouldn't need to use it.
     * @param  string  $path  Defaults to /dev/log
     * @throws \YapepBase\Exception\NotImplementedException because PHP's syslog functions do not accept a log socket.
     */
    public function setPath($path = '/dev/log') {
        throw new \YapepBase\Exception\NotImplementedException('The PHP syslog functions can only use the default '
            . 'log socket.');
    }
    /**
     * Opens the log socket. This implementation does nothing.
     * @return \YapepBase\Syslog\LegacySyslogConnection
     * @throws \YapepBase\Syslog\SyslogException on error 
     */
    public function open() {
        return $this;
    }
    /**
     * Closes the log socket.  This implementation does nothing.
     * @return \YapepBase\Syslog\LegacySyslogConnection 
     */
    public function close() {
        return $this;
    }
    /**
     * Write a log message to the log socket.
     * @param  int     $priority
     * @param  string  $message
     * @param  string  $ident     Defaults to the ident set via setIdent()
     * @param  int     $date      Timestamp for log message. Defaults to now.
     * @return \YapepBase\Syslog\LegacySyslogConnection 
     * @throws \YapepBase\Syslog\SyslogException on error
     * @throws \YapepBase\Exception\NotImplementedException if the date is set, because this implementation
     *                                                      doesn't support it.
     */
    public function log($priority, $message, $ident = null, $date = null) {
        $this->validatePriority($priority);
        $this->validateIdent($ident);
        if (!\is_null($date)) {
            throw new \YapepBase\Exception\NotImplementedException('PHP\'s internal syslog functions don\'t support ' .
                'passing the date');
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
            } else if (isset($e)) {
                throw $e;
            }
        }
        return $this;
    }
}