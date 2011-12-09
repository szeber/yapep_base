<?php

namespace YapepBase\Test\Mock\Syslog;

class SyslogConnectionMock extends \YapepBase\Syslog\SyslogConnection {
    public $isOpen = false;
    public $messages = array();
    protected $throwExceptions = false;
    public function setExceptions($throwExceptions = false) {
        $this->throwExceptions = (bool)$throwExceptions;
    }
    public function open() {
        if ($this->throwExceptions) {
            throw new \YapepBase\Syslog\SyslogException('Mock exception');
        }
        $this->isOpen = true;
        return $this;
    }
    public function close() {
        if ($this->throwExceptions) {
            throw new \YapepBase\Syslog\SyslogException('Mock exception');
        }
        $this->isOpen = false;
        return $this;
    }
    public function log($priority, $message, $ident = null, $date = null) {
        if ($this->throwExceptions) {
            throw new \YapepBase\Syslog\SyslogException('Mock exception');
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
            'priority' => $priority,
            'message' => $message,
            'ident' => $ident,
            'date' => $date
        );
        return $this;
    }
}