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
 * This interface specifies the function calls for all syslog connection classes.
 *
 * @package      YapepBase
 * @subpackage   Syslog
 */
interface ISyslogConnection {

	/**
	 * Log application PID in syslog ident
	 */
	const LOG_PID = 1;

	/**
	 * This function is a replacement for the native openlog() call.
	 *
	 * @param string $ident      The ident.
	 * @param int    $option     Log options, only LOG_PID is recognized.
	 * @param int    $facility   The facility.
	 *
	 * @return bool
	 */
	public function openlog($ident, $option, $facility);

	/**
	 * This function is a replacement for the native syslog() call.
	 *
	 * @param int    $priority   The priority.
	 * @param string $message    The message.
	 *
	 * @return bool
	 */
	public function syslog($priority, $message);

	/**
	 * This function is a replacement for the native closelog() function
	 *
	 * @return bool
	 */
	public function closelog();

	/**
	 * Opens the log socket.
	 *
	 * @return \YapepBase\Syslog\ISyslogConnection
	 */
	public function open();

	/**
	 * Closes the log socket.
	 *
	 * @return \YapepBase\Syslog\NativeSyslogConnection
	 *
	 * @throws \YapepBase\Exception\SyslogException on error
	 */
	public function close();

	/**
	 * Write a log message to the log socket.
	 *
	 * @param int    $priority   The priority.
	 * @param string $message    The message.
	 * @param string $ident      Defaults to the ident set via setIdent()
	 * @param int    $date       Timestamp for log message. Some implementations MAY not respect this.
	 *
	 * @return \YapepBase\Syslog\ISyslogConnection
	 *
	 * @throws \YapepBase\Exception\SyslogException on error
	 * @throws \YapepBase\Exception\NotImplementedException if the date is set, but not supported by the implementation.
	 */
	public function log($priority, $message, $ident = null, $date = null);

	/**
	 * Set the path of the log socket. Some implementations MAY not respect this setting.
	 *
	 * @param string $path   Defaults to /dev/log.
	 *
	 * @return \YapepBase\Syslog\ISyslogConnection
	 *
	 * @throws \YapepBase\Exception\SyslogException on error
	 * @throws \YapepBase\Exception\NotImplementedException if this call is not supported by the implementation.
	 */
	public function setPath($path = '/dev/log');

	/**
	 * Returns the log socket path.
	 *
	 * @return string  The log socket path.
	 *
	 * @throws \YapepBase\Exception\SyslogException on error
	 */
	public function getPath();

	/**
	 * Sets the application identification.
	 *
	 * @param string $ident   The ident.
	 *
	 * @return \YapepBase\Syslog\ISyslogConnection
	 */
	public function setIdent($ident);

	/**
	 * Returns the currently set ident string.
	 *
	 * @return string
	 */
	public function getIdent();

	/**
	 * Sets log options. Only accepts LOG_PID at the moment.
	 *
	 * @param int $options   The options.
	 *
	 * @return \YapepBase\Syslog\NativeSyslogConnection
	 */
	public function setOptions($options);

	/**
	 * Return the options set for logging.
	 *
	 * @return int
	 */
	public function getOptions();

	/**
	 * Set the default facility.
	 *
	 * @param int $facility   The facility.
	 *
	 * @return \YapepBase\Syslog\ISyslogConnection
	 *
	 * @throws \YapepBase\Exception\SyslogException on error
	 */
	public function setFacility($facility);

	/**
	 * Returns the currently set default facility.
	 *
	 * @return  int
	 */
	public function getFacility();
}
