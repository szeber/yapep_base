<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Mock\Syslog
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Mock\Syslog;


use YapepBase\Exception\SyslogException;

/**
 * Mock class for the SyslogConnection
 *
 * @codeCoverageIgnore
 *
 * @package    YapepBase
 * @subpackage Mock\Syslog
 */
class SyslogConnectionMock extends \YapepBase\Syslog\SyslogConnection {

	/** Key to store the priority of the message. */
	const MESSAGE_INDEX_PRIORITY = 'priority';
	/** Key to store the content of the message. */
	const MESSAGE_INDEX_MESSAGE = 'message';
	/** Key to store the ident of the message. */
	const MESSAGE_INDEX_IDENT = 'ident';
	/** Key to store the date of the message. */
	const MESSAGE_INDEX_DATE = 'date';

	/**
	 * Indicates if the connection is opened.
	 *
	 * @var bool
	 */
	public $isOpen = false;

	/**
	 * Stores the messages.
	 *
	 * @var array
	 */
	public $messages = array();

	/**
	 * Indicates if the called methods should throw an Exception.
	 *
	 * @var bool
	 */
	protected $throwExceptions = false;

	/**
	 * Sets the throw exception mode.
	 *
	 * @param bool $throwExceptions   If TRUE, every overridden method will throw an exception.
	 *
	 * @return void
	 */
	public function setExceptions($throwExceptions = false) {
		$this->throwExceptions = (bool)$throwExceptions;
	}

	/**
	 * Opens the log socket.
	 *
	 * @throws \YapepBase\Exception\SyslogException   If the Exception mode is on.
	 *
	 * @return SyslogConnectionMock
	 */
	public function open() {
		if ($this->throwExceptions) {
			throw new SyslogException('Mock exception');
		}
		$this->isOpen = true;
		return $this;
	}

	/**
	 * Closes the log socket.
	 *
	 * @throws \YapepBase\Exception\SyslogException   If the Exception mode is on.
	 *
	 * @return SyslogConnectionMock
	 */
	public function close() {
		if ($this->throwExceptions) {
			throw new SyslogException('Mock exception');
		}
		$this->isOpen = false;
		return $this;
	}

	/**
	 * Write a log message to the log socket.
	 *
	 * @param int    $priority   The priority.
	 * @param string $message    The message.
	 * @param string $ident      Defaults to the ident set via setIdent()
	 * @param int    $date       Timestamp for log message. Some implementations MAY not respect this.
	 *
	 * @throws \YapepBase\Exception\SyslogException   If the Exception mode is on.
	 *
	 * @return SyslogConnectionMock
	 */
	public function log($priority, $message, $ident = null, $date = null) {
		if ($this->throwExceptions) {
			throw new SyslogException('Mock exception');
		}
		if (!$ident) {
			$ident = $this->ident;
		}
		if ($priority < 8) {
			$priority += $this->facility;
		}
		if ($this->options == self::LOG_PID) {
			$ident .= '[pid]';
		}
		$this->messages[] = array(
			self::MESSAGE_INDEX_PRIORITY => $priority,
			self::MESSAGE_INDEX_MESSAGE  => $message,
			self::MESSAGE_INDEX_IDENT    => $ident,
			self::MESSAGE_INDEX_DATE     => $date
		);
		return $this;
	}
}