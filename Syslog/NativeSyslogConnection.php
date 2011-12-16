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
use YapepBase\Exception\SyslogException;

/**
 * This class is a replacement for the native syslog(), openlog() and closelog() calls to make it independent and
 * testable.
 *
 * Warning: do NOT exchange the numeric values to the PHP native syslog constanst!
 */
class NativeSyslogConnection extends SyslogConnection {
    /**
     * Program identification string (tag)
     * @var string
     */
    protected $ident = 'php';
    /**
     * Options for logging (currently only LOG_PID is supported)
     * @var int
     */
    protected $options = 0;
    /**
     * Syslog facility. Must be dividable by 8 by RFC
     * @var int
     */
    protected $facility = 8;
    /**
     * Path of the log socket to use/
     * @var string
     */
    protected $path = '/dev/log';
    /**
     * Open log socket storage
     * @var resource
     */
    protected $sock;

    /**
     * Handle socket errors.
     * @throws SyslogException if a socket error occured.
     */
    protected function handleError() {
        if (\is_resource($this->sock) && socket_last_error($this->sock)) {
            $e = new SyslogException(socket_strerror(socket_last_error($this->sock)), socket_last_error($this->sock));
            socket_clear_error($this->sock);
            throw $e;
        }
    }

    /**
     * Opens the log socket.
     * @return \YapepBase\Syslog\NativeSyslogConnection
     * @throws \YapepBase\Exception\SyslogException on error
     */
    public function open() {
        try {
            $this->sock = @socket_create(AF_UNIX, SOCK_STREAM, 0);
            $this->handleError();
            try {
                @socket_connect($this->sock, $this->path);
            } catch (\Exception $e) {}
            $this->handleError();
        } catch (SyslogException $e) {
            /**
             * If we have a EPROTOTYPE error, the log socket doesn't support stream sockets, only dgram sockets.
             */
            if ($e->getCode() == SOCKET_EPROTOTYPE) {
                try {
                    $this->sock = @socket_create(AF_UNIX, SOCK_DGRAM, 0);
                } catch (\Exception $e) {}
                $this->handleError();
                try {
                    @socket_connect($this->sock, $this->path);
                } catch (\Exception $e) {}
                $this->handleError();
            } else {
                throw $e;
            }
        }
        return $this;
    }

    /**
     * Closes the log socket.
     * @return \YapepBase\Syslog\NativeSyslogConnection
     * @throws \YapepBase\Exception\SyslogException on error
     */
    public function close() {
        if ($this->sock) {
            try {
                @socket_close($this->sock);
            } catch (\Exception $e) {}
            $this->handleError();
        }
        return $this;
    }

    /**
     * Write a log message to the log socket.
     * @param  int     $priority
     * @param  string  $message
     * @param  string  $ident     Defaults to the ident set via setIdent()
     * @param  int     $date      Timestamp for log message. Defaults to now.
     * @return \YapepBase\Syslog\NativeSyslogConnection
     * @throws \YapepBase\Exception\SyslogException on error
     * @todo   Reconnect, if the connection is lost.
     */
    public function log($priority, $message, $ident = null, $date = null) {
        $this->validatePriority($priority);
        $this->validateIdent($ident);
        if ($priority < 8) {
            $priority += $this->facility;
        }
        if (!is_int($date)) {
            $date = time();
        }
        if (!is_string($ident)) {
            $ident = $this->ident;
        }
        $buf = '<' . $priority . '>' . date('M', $date) . ' ' . str_pad(date('j', $date), 2, ' ', STR_PAD_LEFT) . ' ' .
            date('H:i:s', $date) . ' ' . $ident
            . ($this->options&self::LOG_PID && function_exists('posix_getpid')?'[' . posix_getpid() . ']':'') . ': '
            . $message;
        if (!$this->sock) {
            $this->open();
        }
        try {
            @socket_write($this->sock, $buf, 1024);
        } catch (\Exception $e) {}
        $this->handleError();
        return $this;
    }
}