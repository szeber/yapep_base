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
 * This class is a replacement for the native syslog(), openlog() and closelog() calls to make it independent and
 * testable.
 * 
 * Warning: do NOT exchange the numeric values to the PHP native syslog constanst!
 */
class SyslogConnection {
    /**
     * Security/authorization messages
     */
    const LOG_AUTH = 40;
    /**
     * Security/authorization messages (private)
     */
    const LOG_AUTHPRIV = 80;
    /**
     * Cron daemon
     */
    const LOG_CRON = 72;
    /**
     * Other daemons
     */
    const LOG_DAEMON = 24;
    /**
     * FTP server
     */
    const LOG_FTP = 88;
    /**
     * Kernel log (do not use)
     */
    const LOG_KERN = 0;
    /**
     * Local use (ask sysadmin about it)
     */
    const LOG_LOCAL0 = 128;
    /**
     * Local use (ask sysadmin about it)
     */
    const LOG_LOCAL1 = 136;
    /**
     * Local use (ask sysadmin about it)
     */
    const LOG_LOCAL2 = 144;
    /**
     * Local use (ask sysadmin about it)
     */
    const LOG_LOCAL3 = 152;
    /**
     * Local use (ask sysadmin about it)
     */
    const LOG_LOCAL4 = 160;
    /**
     * Local use (ask sysadmin about it)
     */
    const LOG_LOCAL5 = 168;
    /**
     * Local use (ask sysadmin about it)
     */
    const LOG_LOCAL6 = 176;
    /**
     * Local use (ask sysadmin about it)
     */
    const LOG_LOCAL7 = 184;
    /**
     * Printer facility
     */
    const LOG_LPR = 48;
    /**
     * Mail server
     */
    const LOG_MAIL = 16;
    /**
     * News server
     */
    const LOG_NEWS = 72;
    /**
     * Syslog internal messages (do not use)
     */
    const LOG_SYSLOG = 48;
    /**
     * Generic user-level messages
     */
    const LOG_USER = 8;
    /**
     * UUCP subsystem
     */
    const LOG_UUCP = 64;

    /**
     * System is unusable
     */
    const LOG_EMERG = 0;
    /**
     * Action must be taken immediately
     */
    const LOG_ALERT = 1;
    /**
     * Critical conditions
     */
    const LOG_CRIT = 2;
    /**
     * Error conditions
     */
    const LOG_ERR = 3;
    /**
     * Warning conditions
     */
    const LOG_WARNING = 4;
    /**
     * Normal, but significant condition
     */
    const LOG_NOTICE = 5;
    /**
     * Informational message
     */
    const LOG_INFO = 6;
    /**
     * Debug-level message
     */
    const LOG_DEBUG = 7;
    
    /**
     * Log application PID in syslog ident
     */
    const LOG_PID = 1;
    
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
        if (socket_last_error($this->sock)) {
            $e = new SyslogException(socket_strerror(socket_last_error($this->sock)), socket_last_error($this->sock));
            socket_clear_error($this->sock);
            throw $e;
        }
    }
    
    /**
     * This function is a replacement for the native openlog() call.
     * @param  string  $ident
     * @param  int     $option Log options, only LOG_PID is recognized.
     * @param  int     $facility
     * @return SyslogConnection
     */
    public function openlog($ident, $option, $facility) {
        $this->setIdent($ident);
        $this->setOption($option);
        $this->setFacility($facility);
        return $this->open();
    }
    
    /**
     * This function is a replacement for the native syslog() call.
     * @param  int     $priority
     * @param  string  $message
     * @return type 
     */
    public function syslog($priority, $message) {
        return $this->log($priority, $message);
    }
    
    /**
     * This function is a replacement for the native closelog() function
     * @return SyslogConnection
     */
    public function closelog() {
        return $this->close();
    }
    
    /**
     * Opens the log socket.
     * @return SyslogConnection 
     */
    public function open() {
        try {
            $this->sock = socket_create(AF_UNIX, SOCK_STREAM, 0);
            $this->handleError();
            @socket_connect($this->sock, $this->path);
            $this->handleError();
        } catch (\YapepBase\Syslog\SyslogException $e) {
            /**
             * If we have a EPROTOTYPE error, the log socket doesn't support stream sockets, only dgram sockets.
             */
            if ($e->getCode() == SOCKET_EPROTOTYPE) {
                $this->sock = socket_create(AF_UNIX, SOCK_DGRAM, 0);
                $this->handleError();
                @socket_connect($this->sock, $this->path);
                $this->handleError();
            } else {
                throw $e;
            }
        }
        return $this;
    }
    
    /**
     * Closes the log socket.
     * @return SyslogConnection 
     */
    public function close() {
        if ($this->sock) {
            socket_close($$this->sock);
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
     * @return SyslogConnection 
     * @todo   Reconnect, if the connection is lost.
     */
    public function log($priority, $message, $ident = null, $date = null) {
        if (!$this->sock) {
            $this->open();
        }
        if (!is_int($priority) || $priority < 0 || $priority > 191) {
            throw new SyslogException('Invalid priority value ' . $priority);
        }
        if ($priority < 8) {
            $priority += $this->facility;
        }
        if (!is_int($date)) {
            $date = time();
        }
        if (!is_string($ident)) {
            $ident = $this->ident;
        } else if (preg_match('/[\s]/', $ident)) {
            throw new SyslogException('The syslog tag/ident cannot contain whitespace. Value: "' . $ident . '"');
        }
        $buf = '<' . $priority . '>' . date('M', $date) . ' ' . str_pad(date('j', $date), 2, ' ', STR_PAD_LEFT) . ' ' .
            date('H:i:s', $date) . ' ' . $ident
            . ($this->options&self::LOG_PID && function_exists('posix_getpid')?'[' . posix_getpid() . ']':'') . ': '
            . $message;
        socket_write($this->sock, $buf, 1024);
        $this->handleError();
        return $this;
    }
    
    /**
     * Set the path of the log socket. Only works before open()/openlog(). Normally you shouldn't need to use it.
     * @param  string  $path  Defaults to /dev/log
     * @return SyslogConnection 
     */
    public function setPath($path = '/dev/log') {
        $this->path = (string)$path;
        return $this;
    }
    
    /**
     * Returns the log socket path.
     * @return  string  The log socket path.
     */
    public function getPath() {
        return $this->path;
    }
    
    /**
     * Sets the application identification.
     * @param  string  $ident
     * @return SyslogConnection 
     */
    public function setIdent($ident) {
        if (preg_match('/[\s]/', $ident)) {
            throw new SyslogException('The syslog tag/ident cannot contain whitespace. Value: "' . $ident . '"');
        }
        $this->ident = (string)$ident;
        return $this;
    }
    
    /**
     * Returns the currently set ident string.
     * @return string
     */
    public function getIdent() {
        return $this->ident;
    }
    
    /**
     * Sets log options. Only accepts LOG_PID at the moment.
     * @param  int  $options
     * @return SyslogConnection 
     */
    public function setOptions($options) {
        $this->options = (int)$options;
        return $this;
    }
    
    /**
     * Return the options set for logging.
     * @return  int
     */
    public function getOptions() {
        return $this->options;
    }
    
    /**
     * Set the default facility.
     * @param  int  $facility
     * @return SyslogConnection 
     */
    public function setFacility($facility) {
        if ($facility % 8 == 0 && $facility >= 0 && $facility <= 184) {
            $this->facility = $facility;
            return $this;
        } else {
            throw new \YapepBase\Exception\ParameterException('Invalid facility: ' . $facility
                . ' (facilities must be dividable by 8 and between 0 and 184)');
        }
    }
    
    /**
     * Returns the currently set default facility.
     * @return  int
     */
    public function getFacility() {
        return $this->facility;
    }
}