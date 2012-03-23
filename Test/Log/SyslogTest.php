<?php

namespace YapepBase\Log;

use YapepBase\Config;

/**
 * Test class for Syslog.
 */
class SyslogTest extends \PHPUnit_Framework_TestCase {

    public function setUp() {
        parent::setUp();
        Config::getInstance()->clear();
    }

    public function testLog() {
        Config::getInstance()->set('syslog', array(
            'applicationIdent' => 'testApp',
            'facility' => \YapepBase\Syslog\Syslog::LOG_USER,
        ));
        $mock = new \YapepBase\Test\Mock\Syslog\SyslogConnectionMock();
        $this->assertFalse($mock->isOpen);
        $o = new SyslogLogger('syslog', $mock);
        $this->assertTrue($mock->isOpen);

        $msg = new Message\ErrorMessage();
        $msg->set('Test message', 'test', 'test', \YapepBase\Syslog\Syslog::LOG_NOTICE);
        $o->log($msg);

        $this->assertEquals(array(
            array(
                'priority' => \YapepBase\Syslog\Syslog::LOG_NOTICE + \YapepBase\Syslog\Syslog::LOG_USER,
                'message' => '[phpErrorLog]|test|test|Test message',
                'ident' => 'testApp',
                'date' => null
            )
        ), $mock->messages);
    }

    public function testLogInstantiationWithArray() {
        Config::getInstance()->set('syslog', array(
            'applicationIdent' => 'testApp',
            'facility' => \YapepBase\Syslog\Syslog::LOG_USER,
        ));
        $mock = new \YapepBase\Test\Mock\Syslog\SyslogConnectionMock();
        $o = new SyslogLogger('syslog', $mock);
    }

    public function testLogInstantiationWithHierarchicalConfig() {
        Config::getInstance()->set(array(
            'syslog.applicationIdent' => 'testApp',
            'syslog.facility' => \YapepBase\Syslog\Syslog::LOG_USER,
        ));
        $mock = new \YapepBase\Test\Mock\Syslog\SyslogConnectionMock();
        $o = new SyslogLogger('syslog.*', $mock);

    }

    public function testLogWithPidAndSapi() {
        Config::getInstance()->set('syslog', array(
            'applicationIdent' => 'testApp',
            'facility' => \YapepBase\Syslog\Syslog::LOG_USER,
            'includeSapiName' => true,
            'addPid' => true,
            ));
        $mock = new \YapepBase\Test\Mock\Syslog\SyslogConnectionMock();
        $o = new SyslogLogger('syslog', $mock);
        $msg = new Message\ErrorMessage();
        $msg->set('Test message', 'test', 'test', \YapepBase\Syslog\Syslog::LOG_NOTICE);
        $o->log($msg);

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
        $mock = new \YapepBase\Test\Mock\Syslog\SyslogConnectionMock();

        Config::getInstance()->set('syslog', array(
            'facility' => \YapepBase\Syslog\Syslog::LOG_USER,
            ));
        try {
            $o = new SyslogLogger('syslog', $mock);
            $this->fail('Calling syslog class without applicationIdent config should result in a ConfigException');
        } catch (\YapepBase\Exception\ConfigException $e) { }

        Config::getInstance()->set('syslog', array(
            'applicationIdent' => 'testApp',
            ));
        try {
            $o = new SyslogLogger('syslog', $mock);
            $this->fail('Calling syslog class without facility config should result in a ConfigException');
        } catch (\YapepBase\Exception\ConfigException $e) { }

        Config::getInstance()->set('syslog', array(
            ));
        try {
            $o = new SyslogLogger('syslog', $mock);
            $this->fail('Calling syslog class without facility and applicationIdent config should result in a ConfigException');
        } catch (\YapepBase\Exception\ConfigException $e) { }

        try {
            $o = new SyslogLogger('nonexistent', $mock);
            $this->fail('Calling syslog class without config should result in a ConfigException');
        } catch (\YapepBase\Exception\ConfigException $e) { }
    }

    public function testMissingConfiguration() {
        $mock = new \YapepBase\Test\Mock\Syslog\SyslogConnectionMock();

        try {
            $o = new SyslogLogger('syslogNonExistent', $mock);
            $this->fail('Calling syslog class with nonexistent config name should resilt in a ConfigException');
        } catch (\YapepBase\Exception\ConfigException $e) { }
    }
}
