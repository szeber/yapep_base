<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Batch
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Batch;

use YapepBase\Config;
use YapepBase\Exception\Exception;
use YapepBase\Exception\ParameterException;

/**
 * Base class for creating batch scripts that should be ran in at most one instance.
 *
 * Uses the CliUserInterfaceHelper to parse/handle switches. Any subclasses should not use these switches for their
 * configuration, or override the parseSwitches() method.
 *
 * When overriding the prepareSwitches() and parseSwitches() methods, be careful, that no locking is done yet when
 * these methods are called.
 *
 * The following switches are defined and parsed by the class:
 * <ul>
 *     <li>--pid-path: The full path to the PID file storage directory. Optional, defaults to the value of the
 *                     config option "system.path.batchPid", or if not set to /var/run.</li>
 *     <li>--pid-file: The name of the PID file without path. Optional, defaults to the name of the script with .pid
 *                     extension.</li>
 *     <li>--help: If set, the help page will be printed.</li>
 * </ul>
 *
 * Configuration options:
 * <ul>
 *     <li>system.path.batchPid: The full path to the default PID storage directory.
 *                                    Optional, defaults to /var/run</li>
 * </ul>
 *
 * @package    YapepBase
 * @subpackage Batch
 */
abstract class LockingBatchScript extends BatchScript {
	/**
	 * The full path of the PID file.
	 *
	 * @var string
	 */
	protected $pidPath;

	/**
	 * The filename of the PID file.
	 *
	 * @var string
	 */
	protected $pidFile;

	/**
	 * Stores the lock file descriptor
	 *
	 * @var resource
	 */
	protected $lockFileDescriptor;

	/**
	 * Called before the run process.
	 *
	 * Please be cautious! In case of overriding, don't forget to call this method first.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\Exception   On errors.
	 */
	protected function runBefore() {
		parent::runBefore();
		if (!$this->acquireLock()) {
			throw new Exception('Acquire lock failed!');
		}
	}

	/**
	 * Called after the run process.
	 *
	 * Please be cautious! In case of overriding, don't forget to call this method in the end.
	 *
	 * @return void
	 */
	protected function runAfter() {
		$this->releaseLock();
		parent::runAfter();
	}

	/**
	 * Starts script execution.
	 *
	 * @return void
	 */
	public function run() {
		try {
			parent::run();
		}
		catch (\Exception $exception) {
			$this->removeSignalHandler();
			$this->releaseLock();
			// Re-throw the exception;
			throw $exception;
		}

		$this->releaseLock();
	}

	/**
	 * Sets the switches used by the script
	 *
	 * @return void
	 */
	protected function prepareSwitches() {
		parent::prepareSwitches();

		$this->cliHelper->addSwitch(null, 'pid-path', 'The full path to the PID file storage directory.
			Optional, defaults to the value of the config option "application.batch.pid", or if not set to /var/run.',
			null, true, 'pidPath', false);

		$this->cliHelper->addSwitch(null, 'pid-file',
			'The name of the PID file without path. Optional, defaults to the name of the script with .pid extension.',
			null, true, 'pidFile', false);
	}

	/**
	 * Parses the switches used by the script.
	 *
	 * @param array $switches   The parsed switches.
	 *
	 * @return bool   Returns TRUE, if the validation of the provided switches was successful.
	 *
	 * @throws \YapepBase\Exception\ParameterException   If there are errors regarding the PID file
	 */
	protected function parseSwitches(array $switches) {
		parent::parseSwitches($switches);

		$config = Config::getInstance();

		$this->pidPath = (empty($switches['pid-path']) ? $config->get('system.path.batchPid', '/var/run')
			: $switches['pid-path']);
		$this->pidFile = (empty($switches['pid-file'])
			? preg_replace('/\.php$/', '', basename($_SERVER['argv'][0])) . '.pid'
			: $switches['pid-file']
		);

		if (!is_dir($this->pidPath)) {
			throw new ParameterException('The pid path does not exist: ' . $this->pidPath);
		}

		if (
			!is_writable($this->pidPath)
			|| (file_exists($this->getFullPidFile()) && !is_writable($this->getFullPidFile()))
		) {
			throw new ParameterException('The pid file is not writable: ' . $this->getFullPidFile());
		}
	}

	/**
	 * Returns the full path of the PID file
	 *
	 * @return string
	 */
	protected function getFullPidFile() {
		return $this->pidPath . DIRECTORY_SEPARATOR . $this->pidFile;
	}

	/**
	 * Acquires the lock on the PID file and inserts the PID.
	 *
	 * @throws \YapepBase\Exception\Exception   If something went wrong.
	 *
	 * @return bool
	 */
	protected function acquireLock() {
		$pidFile = $this->getFullPidFile();
		if (!$fp = fopen($pidFile, 'a+')) {
			// We can't open the PID file
			throw new Exception('Can\'t open PID file: ' . $pidFile);
		}
		if (!flock($fp, LOCK_EX | LOCK_NB)) {
			// File is locked by an other instance, skip this run.
			fclose($fp);
			return false;
		}
		ftruncate($fp, 0);
		if (function_exists('posix_getpid')) {
			fwrite($fp, posix_getpid() . PHP_EOL);
		}
		$this->lockFileDescriptor = $fp;
		return true;
	}

	/**
	 * Truncates and releases the lock on the PID file.
	 *
	 * @return void
	 */
	protected function releaseLock() {
		if ($this->lockFileDescriptor) {
			$fp = $this->lockFileDescriptor;
			$this->lockFileDescriptor = null;
			ftruncate($fp, 0);
			flock($fp, LOCK_UN);
			fclose($fp);
		}
	}

}