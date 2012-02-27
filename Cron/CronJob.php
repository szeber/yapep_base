<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Cron
 * @author       Janos Pasztor <j.pasztor@ixolit.com>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

declare(ticks = 1) ;

namespace YapepBase\Cron;

/**
 * This is an abstract class for cronjobs.
 *
 * Usage example:
 *
 * class MyCronJob extends CronJob {
 *     function work() {
 *         //do something
 *     }
 * }
 *
 * $myCronJob = new MyCronJob();
 * $myCronJob->run();
 *
 * Advanced usage:
 *
 * $myCronJob = new MyCronJob();
 * $myCronJob->setPidPath('/tmp');
 * $myCronJob->setPidFile('my-cron-job.pid');
 * $myCronJob->run();
 *
 * @abstract
 */
abstract class CronJob {

    /**
     * Stores the file name of the PID file
     *
     * @var string
     */
    protected $pidFile = '';

    /**
     * Stores the path of the PID file
     *
     * @var string
     */
    protected $pidPath = '/var/run';

    /**
     * Stores the lock file descriptor
     *
     * @var resource
     */
    protected $lockFd;

    /**
     * Handle signals in the signal handler.
     *
     * @var bool
     */
    protected $handleSignals = false;

    /**
     * PHP 5 constructor
     */
    public function __construct() {
        $this->pidFile = trim(strtr(get_class($this), '\\', '_'), '_') . '.pid';
        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, array(&$this, "handleSignal"), true);
            pcntl_signal(SIGHUP, array(&$this, "handleSignal"), true);
            pcntl_signal(SIGINT, array(&$this, "handleSignal"), true);
        }
    }

    /**
     * Sets the PID file name (file name only). Normally this is not needed, the class name is used.
     *
     * @param string $pidFile
     */
    public function setPidFile($pidFile) {
        $this->pidFile = $pidFile;
    }

    /**
     * Get the PID file name.
     *
     * @return string
     */
    public function getPidFile() {
        return $this->pidFile;
    }

    /**
     * Set the PID file path. This MUST be on a filesystem, that supports locking.
     * DO NOT USE NFS FOR IT! You have been warned.
     *
     * @param string $pidPath
     */
    public function setPidPath($pidPath) {
        $this->pidPath = $pidPath;
    }

    /**
     * Get the PID file path
     *
     * @return string
     */
    public function getPidPath() {
        return $this->pidPath;
    }

    /**
     * Returns the full path of the PID file
     *
     * @return string
     */
    public function getFullPidFile() {
        return $this->pidPath . DIRECTORY_SEPARATOR . $this->pidFile;
    }

    /**
     * Acquires the lock on the PID file and inserts the PID.
     *
     * @return boolean
     */
    protected function acquireLock() {
        $pidFile = $this->getFullPidFile();
        if (!$fp = fopen($pidFile, 'a+')) {
            //We can't open the PID file
            //@codeCoverageIgnoreStart
            trigger_error('Can\'t open PID file: ' . $pidFile, E_USER_WARNING);
            return false;
            //@codeCoverageIgnoreEnd
        }
        if (!flock($fp, LOCK_EX | LOCK_NB)) {
            //File is locked by an other instance, skip this run.
            fclose($fp);
            return false;
        }
        ftruncate($fp, 0);
        if (function_exists('posix_getpid')) {
            fwrite($fp, posix_getpid() . PHP_EOL);
        }
        $this->lockFd = $fp;
        return true;
    }

    /**
     * Truncates and releases the lock on the PID file.
     */
    protected function releaseLock() {
        if ($this->lockFd) {
            $fp           = $this->lockFd;
            $this->lockFd = null;
            ftruncate($fp, 0);
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }

    /**
     * Acquires the lock, runs the cronjob and then releases it.
     */
    final public function run() {
        if ($this->acquireLock()) {
            $this->setSignalHandler();
            try {
                $this->work();
            } catch (\Exception $e) { }
            $this->removeSignalHandler();
            $this->releaseLock();
            if (isset($e)) {
                throw $e;
            }
        }
    }

    /**
     * Sets the signal handlers
     */
    protected function setSignalHandler() {
        $this->handleSignals = true;
    }

    /**
     * Removes the signal handlers
     */
    protected function removeSignalHandler() {
        $this->handleSignals = false;
    }

    /**
     * Signal handler
     * @codeCoverageIgnore
     */
    final protected function handleSignal($signo) {
        if ($this->handleSignals) {
            switch ($signo) {
                case SIGTERM:
                case SIGHUP:
                case SIGINT:
                    $this->abort();
                    $this->releaseLock();
                    exit;
                    break;
            }
        }
    }

    /**
     * This function is called, if the process receives an interrupt, term signal, etc. It can be used to clean up
     * stuff. Note, that this function is not guaranteed to run or it may run after execution.
     *
     * @codeCoverageIgnore
     */
    protected function abort() {

    }

    /**
     * Function to do the actual work.
     */
    abstract protected function work();
}
