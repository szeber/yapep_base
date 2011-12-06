<?php

namespace YapepBase\Log;

use YapepBase\Config;

/**
 * Test class for Syslog.
 */
class SyslogTest extends \PHPUnit_Framework_TestCase {
    public function testLog() {
        $this->markTestIncomplete();
        Config::getInstance()->set('syslog', array(
            'applicationIdent' => 'testApp',
            'facility' => LOG_USER,
            ));
        $o = new Syslog('syslog');
        $msg = new Message\ErrorMessage();
        $msg->set('Test message', 'test', 'test', \LOG_NOTICE);
        if (!file_exists(__dir__ . '/../Temp/Log/dev/')) {
            mkdir(__dir__ . '/../Temp/Log/dev/');
        }
        if (!\chroot(__dir__ . '/../Temp/Log/')) {
            $this->markTestSkipped('Failed to chroot, skipping test.');
            return;
        }
        $sock = socket_create(AF_UNIX, SOCK_DGRAM, 0);
        socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($sock, '/dev/log');
        socket_listen($sock);
        $pid = pcntl_fork();
        if ($pid < 0) {
            $this->markTestSkipped('Failed to fork, skipping test.');
            return;
        } elseif ($pid == 0) {
            $o->log($msg);
            exit;
        } else {
            $client = socket_accept($sock);
            if ($client !== false) {
                $data = socket_read($sock, 1024);
                var_dump($data);
            }
            socket_close($client);
            socket_close($sock);
        }
    }
}
