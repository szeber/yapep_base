<?php

namespace YapepBase\Log;

use YapepBase\Config;

/**
 * Test class for Syslog.
 */
class SyslogTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		parent::setUp();
		Config::getInstance()->set(array(
			'resource.log.default.applicationIdent' => 'testApp',
			'resource.log.default.facility'         => \YapepBase\Syslog\Syslog::LOG_USER,

			'resource.log.pidSapi.applicationIdent' => 'testApp',
			'resource.log.pidSapi.facility'         => \YapepBase\Syslog\Syslog::LOG_USER,
			'resource.log.pidSapi.includeSapiName'  => true,
			'resource.log.pidSapi.addPid'           => true,

			'resource.log.noIdent.facility'         => LOG_USER,

			'resource.log.noFacility.applicationIdent' => 'testApp',

		));
	}

	public function tearDown() {
		Config::getInstance()->clear();
	}

	public function testLog() {
		$mock = new \YapepBase\Mock\Syslog\SyslogConnectionMock();
		$this->assertFalse($mock->isOpen);
		$syslog = new SyslogLogger('default', $mock);
		$this->assertTrue($mock->isOpen);

		$msg = new Message\ErrorMessage();
		$msg->set('Test message', 'test', 'test', \YapepBase\Syslog\Syslog::LOG_NOTICE);
		$syslog->log($msg);

		$this->assertEquals(array(
			array(
				'priority' => \YapepBase\Syslog\Syslog::LOG_NOTICE + \YapepBase\Syslog\Syslog::LOG_USER,
				'message' => '[phpErrorLog]|test|test|Test message',
				'ident' => 'testApp',
				'date' => null
			)
		), $mock->messages);
	}

	public function testLogInstantiation() {
		$mock = new \YapepBase\Mock\Syslog\SyslogConnectionMock();
		new SyslogLogger('default', $mock);
	}

	public function testLogWithPidAndSapi() {
		$mock = new \YapepBase\Mock\Syslog\SyslogConnectionMock();
		$syslog = new SyslogLogger('pidSapi', $mock);
		$msg = new Message\ErrorMessage();
		$msg->set('Test message', 'test', 'test', \YapepBase\Syslog\Syslog::LOG_NOTICE);
		$syslog->log($msg);

		$this->assertEquals(array(
			array(
				'priority' => \YapepBase\Syslog\Syslog::LOG_NOTICE + \YapepBase\Syslog\Syslog::LOG_USER,
				'message' => '[phpErrorLog]|test|test|Test message',
				'ident' => 'testApp-' . PHP_SAPI . '[pid]',
				'date' => null
			)
		), $mock->messages);
	}

	public function testInvalidConfiguration() {
		$mock = new \YapepBase\Mock\Syslog\SyslogConnectionMock();

		try {
			new SyslogLogger('noIdent', $mock);
			$this->fail('Calling syslog class without applicationIdent config should result in a ConfigException');
		} catch (\YapepBase\Exception\ConfigException $e) {
		}

		try {
			new SyslogLogger('noFacility', $mock);
			$this->fail('Calling syslog class without facility config should result in a ConfigException');
		} catch (\YapepBase\Exception\ConfigException $e) {
		}

		try {
			new SyslogLogger('nonexistent', $mock);
			$this->fail('Calling syslog class without config should result in a ConfigException');
		} catch (\YapepBase\Exception\ConfigException $e) {
		}
	}
}
