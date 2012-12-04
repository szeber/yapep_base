<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Log
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Log;


use YapepBase\Config;
use YapepBase\Log\SyslogLogger;
use YapepBase\Mock\Log\Message\MessageMock;
use YapepBase\Mock\Log\SyslogLoggerMock;
use YapepBase\Mock\Syslog\SyslogConnectionMock;

/**
 * Test class for the SyslogLogger class
 *
 * @package    YapepBase
 * @subpackage Log
 */
class SyslogLoggerTest extends \PHPUnit_Framework_TestCase {

	/** The name of the config entry for the syslog connection. */
	const SYSLOG_CONNECTION_CONFIG_NAME = 'test';

	/** The application indent for the test syslog connection. */
	const SYSLOG_CONNECTION_APPLICATION_INDENT = 'testApplication';

	/**
	 * The SyslogLogger object to test
	 *
	 * @var \YapepBase\Mock\Log\SyslogLoggerMock
	 */
	protected $syslogLogger;

	/**
	 * The syslog connection mock what is being used by the logger.
	 *
	 * @var \YapepBase\Mock\Syslog\SyslogConnectionMock
	 */
	protected $syslogConnectionMock;

	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp() {
		Config::getInstance()->set(array(
			'resource.log.' . self::SYSLOG_CONNECTION_CONFIG_NAME . '.facility'         => LOG_LOCAL5,
			'resource.log.' . self::SYSLOG_CONNECTION_CONFIG_NAME . '.applicationIdent'
				=> self::SYSLOG_CONNECTION_APPLICATION_INDENT,
			'resource.log.' . self::SYSLOG_CONNECTION_CONFIG_NAME . '.includeSapiName'  => false,
			'resource.log.' . self::SYSLOG_CONNECTION_CONFIG_NAME . '.addPid'           => false,

		));

		$this->syslogConnectionMock = new SyslogConnectionMock();
		$this->syslogLogger = new SyslogLoggerMock(self::SYSLOG_CONNECTION_CONFIG_NAME, $this->syslogConnectionMock);
	}

	/**
	 * Tests the getLogMessage() method.
	 *
	 * @return void
	 */
	public function testGetLogMessage(){
		$message = 'Test Message';
		$field1 = 'test1';
		$field2 = 'test2';
		$messageObject = new MessageMock($message, $field1, $field2);
		$logEntry = $this->syslogLogger->getLogMessage($messageObject);

		$expectedLogEntry =
			'[' . MessageMock::TAG . ']'
			. '|' . MessageMock::FIELD_1 . '=' . $field1
			. '|' . MessageMock::FIELD_2 . '=' . $field2
			. '|' . 'message=' . $message
		;

		$this->assertEquals($expectedLogEntry, $logEntry);
	}
}