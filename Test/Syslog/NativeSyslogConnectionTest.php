<?php

namespace YapepBase\Syslog;

/**
 * Test class for NativeSyslogConnection.
 * Generated by PHPUnit on 2011-12-06 at 11:33:00.
 */
class NativeSyslogConnectionTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var NativeSyslogConnection
     */
    protected $object;

    protected $logpath;
    protected $sock;
    protected $dgram;

    protected function setUp() {
        $this->object = new NativeSyslogConnection();
    }

    protected function initSyslogServer($logpath, $dgram = false) {
        try {
            if (\file_exists($logpath)) {
                \unlink($logpath);
            }
            if ($dgram) {
                $this->sock = \socket_create(AF_UNIX, SOCK_DGRAM, 0);
            } else {
                $this->sock = \socket_create(AF_UNIX, SOCK_STREAM, 0);
            }
            \socket_set_option($this->sock, SOL_SOCKET, SO_REUSEADDR, 1);
            \socket_bind($this->sock, $logpath);
            if (!$dgram) {
                \socket_listen($this->sock);
            }
            $this->logpath = $logpath;
            $this->dgram = $dgram;
        } catch (\PHPUnit_Framework_Error_Warning $e) {
            $this->markTestSkipped('Unable to create log socket');
        }
    }

    protected function getSyslogMessage() {
        if (!$this->dgram) {
            $client = \socket_accept($this->sock);
            if ($client !== false) {
                $msg = \socket_read($client, 1024);
                \socket_close($client);
                return $msg;
            }
        } else {
            if (($msg = \socket_read($this->sock, 1024))) {
                return $msg;
            }
        }
        return false;
    }

    protected function closeSyslogServer() {
        \socket_close($this->sock);
        if (\file_exists($this->logpath)) {
            \unlink($this->logpath);
        }
    }

    public function testPath() {
        $this->object->setPath('/test');
        $this->assertEquals('/test', $this->object->getPath());
    }

    public function testIdent() {
        $this->object->setIdent('identtest');
        $this->assertEquals('identtest', $this->object->getIdent());
    }

    public function testOptions() {
        $this->object->setOptions(ISyslogConnection::LOG_PID);
        $this->assertEquals(ISyslogConnection::LOG_PID, $this->object->getOptions());
    }

    public function testFacility() {
        $this->object->setFacility(Syslog::LOG_AUTH);
        $this->assertEquals(Syslog::LOG_AUTH, $this->object->getFacility());

        try {
            $this->object->setFacility(192);
            $this->fail('Setting an invalid facility should result in a ParameterException');
        } catch (\YapepBase\Exception\ParameterException $e) { }

        try {
            $this->object->setFacility(-1);
            $this->fail('Setting an invalid facility should result in a ParameterException');
        } catch (\YapepBase\Exception\ParameterException $e) { }

        try {
            $this->object->setFacility(7);
            $this->fail('Setting an invalid facility should result in a ParameterException');
        } catch (\YapepBase\Exception\ParameterException $e) { }
    }

    public function testLogging() {
        $logpath = \dirname(__DIR__) . '/Temp/Syslog/log';
        $this->object->setFacility(Syslog::LOG_USER);
        $this->object->setPath($logpath);
        $this->object->setIdent('test');

        $this->initSyslogServer($logpath);
        $this->object->open();

        $this->object->log(Syslog::LOG_NOTICE, 'test', 'test', mktime(15, 45, 19, 12, 6, 2011));
        $this->assertEquals('<13>Dec  6 15:45:19 test: test', $this->getSyslogMessage());
        $this->object->close();
        $this->closeSyslogServer();
    }

    public function testLoggingWithNoParams() {
        $logpath = \dirname(__DIR__) . '/Temp/Syslog/log';

        $this->initSyslogServer($logpath);
        $this->object->setPath($logpath);
        $this->object->open();

        $this->object->log(Syslog::LOG_NOTICE, 'test');
        $this->assertRegExp('/^<13>(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) ( |1|2|3)([0-9]) ([0-9]{2}):([0-9]{2}):([0-9]{2}) php: test$/', $this->getSyslogMessage());
        $this->object->close();
        $this->closeSyslogServer();
    }

    public function testHandleError() {
        try {
            $this->object->setPath('/nonexistent');
            $this->object->open();
            $this->fail('Connecting to a non-existent socket should result in a SyslogException');
        } catch (\YapepBase\Exception\SyslogException $e) { }
    }

    public function testInvalidParams() {
        try {
            $this->object->setIdent('Test Ident');
            $this->fail('setIdent with whitespace should result in a SyslogException');
        } catch (\YapepBase\Exception\SyslogException $e) { }
        try {
            $this->object->log(Syslog::LOG_NOTICE, 'test', 'Test Ident');
            $this->fail('log with ident with whitespace should result in a SyslogException');
        } catch (\YapepBase\Exception\SyslogException $e) { }
        try {
            $this->object->log(-1, 'test');
            $this->fail('Negative priority should result in a SyslogException');
        } catch (\YapepBase\Exception\SyslogException $e) { }
        try {
            $this->object->log(192, 'test');
            $this->fail('Priority higher than 192 should result in a SyslogException');
        } catch (\YapepBase\Exception\SyslogException $e) { }
        try {
            $this->object->log(0.1, 'test');
            $this->fail('Float priority should result in a SyslogException');
        } catch (\YapepBase\Exception\SyslogException $e) { }
    }

    public function testDgramSockets() {
        $logpath = \dirname(__DIR__) . '/Temp/Syslog/log';
        $this->object->setFacility(Syslog::LOG_USER);
        $this->object->setPath($logpath);
        $this->object->setIdent('test');

        $this->initSyslogServer($logpath, true);

        $this->object->log(Syslog::LOG_NOTICE, 'test', 'test', mktime(15, 45, 19, 12, 6, 2011));
        $this->assertEquals('<13>Dec  6 15:45:19 test: test', $this->getSyslogMessage());
        $this->object->close();
        $this->closeSyslogServer();
    }

    public function testLegacyCalls() {
        $logpath = \dirname(__DIR__) . '/Temp/Syslog/log';
        $this->object->setPath($logpath);

        $this->initSyslogServer($logpath, true);
        $this->object->openlog('test', 0, Syslog::LOG_LOCAL0);

        $this->object->syslog(Syslog::LOG_NOTICE, 'test');
        $this->assertRegExp('/^<133>(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) ( |1|2|3)([0-9]) ([0-9]{2}):([0-9]{2}):([0-9]{2}) test: test$/', $this->getSyslogMessage());
        $this->object->closelog();
        $this->closeSyslogServer();
    }
}