<?php

namespace YapepBase\Test\Mock\Cron;

/**
 * @codeCoverageIgnore
 */
class CronJobMock extends \YapepBase\Cron\CronJob {
    public $hasWorked = false;
    public function work() {
        $this->hasWorked = true;
    }
    public function startFakeRun() {
        return $this->acquireLock();
    }
    public function stopFakeRun() {
        return $this->releaseLock();
    }
}