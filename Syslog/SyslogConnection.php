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
 * This class specifies the function calls for all syslog connection classes
 */
abstract class SyslogConnection implements ISyslogConnection {
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
     * This function is a replacement for the native openlog() call.
     * @param  string  $ident
     * @param  int     $option Log options, only LOG_PID is recognized.
     * @param  int     $facility
     * @return bool
     */
    public function openlog($ident, $option, $facility) {
        try {
            $this->setIdent($ident);
            $this->setOptions($option);
            $this->setFacility($facility);
            $this->open();
            return true;
        } catch (SyslogException $e) {
            return false;
        }
    }

    /**
     * This function is a replacement for the native syslog() call.
     * @param  int     $priority
     * @param  string  $message
     * @return bool
     */
    public function syslog($priority, $message) {
        try {
            $this->log($priority, $message);
            return true;
        } catch (SyslogException $e) {
            return false;
        }

    }

    /**
     * This function is a replacement for the native closelog() function
     * @return bool
     */
    public function closelog() {
        try {
            $this->close();
            return true;
        } catch (SyslogException $e) {
            return false;
        }
    }
    /**
     * Set the path of the log socket. Some implementations MAY not respect this setting.
     * @param  string  $path  Defaults to /dev/log
     * @return \YapepBase\Syslog\ISyslogConnection
     * @throws \YapepBase\Exception\SyslogException on error
     * @throws \YapepBase\Exception\NotImplementedException if this call is not supported by the implementation.
     */
    public function setPath($path = '/dev/log') {
        $this->path = (string)$path;
        return $this;
    }

    /**
     * Returns the log socket path.
     * @return string  The log socket path.
     * @throws \YapepBase\Exception\SyslogException on error
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Sets the application identification.
     * @param  string  $ident
     * @return \YapepBase\Syslog\SyslogConnection
     */
    public function setIdent($ident) {
        $this->validateIdent($ident);
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
     * @return \YapepBase\Syslog\SyslogConnection
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
     * @return \YapepBase\Syslog\SyslogConnection
     * @throws \YapepBase\Exception\SyslogException on error
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
     * @return int
     */
    public function getFacility() {
        return $this->facility;
    }

    /**
     * Validates the value of the priority field.
     * @param int $priority
     * @throws \YapepBase\Exception\SyslogException if the value is invalid.
     */
    protected function validatePriority($priority) {
        if (!is_int($priority) || $priority < 0 || $priority > 191) {
            throw new SyslogException('Invalid priority value ' . $priority);
        }
    }

    /**
     * Validates the value of the ident field
     * @param string $ident
     */
    protected function validateIdent($ident) {
        if (preg_match('/[\s]/', $ident)) {
            throw new SyslogException('The syslog tag/ident cannot contain whitespace. Value: "' . $ident . '"');
        }
    }
}
