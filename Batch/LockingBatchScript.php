<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Batch
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

// Set the ticks, so we can handle signals
declare(ticks = 1) ;

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
 *                     config option "application.path.batchPid", or if not set to /var/run.</li>
 *     <li>--pid-file: The name of the PID file without path. Optional, defaults to the name of the script with .pid
 *                     extension.</li>
 *     <li>--help: If set, the help page will be printed.</li>
 * </ul>
 *
 * The following switches are defuned, but not parsed by the class:
 * <ul>
 *     <li>-e: Name of the execution environment. It should be used by the bootstrap. Whether it's required is dependant on the
 *             script's bootstrap.</li>
 * </ul>
 *
 * Configuration options:
 * <ul>
 *     <li>application.path.batchPid: The full path to the default PID storage directory.
 *                                    Optional, defaults to /var/run</li>
 * </ul>
 *
 * @package    YapepBase
 * @subpackage Batch
 */
abstract class LockingBatchScript extends BatchScript {

	/** Help usage. */
	const HELP_USAGE = 'help';

	/**
	 * The CliUserInterfaceHelper instance.
	 *
	 * @var CliUserInterfaceHelper
	 */
	protected $cliHelper;

	/**
	 * The index for the help usage.
	 *
	 * @var int
	 */
	protected $usageIndexes = array();

	/**
	 * The currently used usage.
	 *
	 * @var string
	 */
	protected $currentUsage;

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
	 * Handle signals in the signal handler.
	 *
	 * @var bool
	 */
	protected $handleSignals = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->cliHelper = new CliUserInterfaceHelper($this->getScriptDescription());
		if (function_exists('pcntl_signal')) {
			pcntl_signal(SIGTERM, array(&$this, "handleSignal"), true);
			pcntl_signal(SIGHUP, array(&$this, "handleSignal"), true);
			pcntl_signal(SIGINT, array(&$this, "handleSignal"), true);
		}
	}

	/**
	 * Starts script execution.
	 */
	protected function runScript() {
		$this->prepareSwitches();
		try {
			$this->parseSwitches($this->cliHelper->getParsedArgs());
		} catch (\Exception $exception) {
			$this->cliHelper->setErrorMessage($exception->getMessage());
			echo $this->cliHelper->getUsageOutput(false);
			// Re-throw the exception
			throw $exception;
		}
		if ($this->acquireLock()) {
			try {
				if ($this->currentUsage == self::HELP_USAGE) {
					echo $this->cliHelper->getUsageOutput(true);
				} else {
					parent::runScript();
				}
			} catch (\Exception $exception) {
				$this->removeSignalHandler();
				$this->releaseLock();
				// Re-throw the exception;
				throw $exception;
			}
		}
		$this->removeSignalHandler();
		$this->releaseLock();
	}

	/**
	 * Sets the switches used by the script
	 */
	protected function prepareSwitches() {
		$this->usageIndexes[self::HELP_USAGE] = $this->cliHelper->addUsage('Help');

		$this->cliHelper->addSwitch('e', null, 'Name of the execution environment', null, false, 'environment', false);

		$this->cliHelper->addSwitch(null, 'help', 'Shows the help', $this->usageIndexes[self::HELP_USAGE]);

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
	 */
	protected function parseSwitches(array $switches) {
		$config = Config::getInstance();

		$this->pidPath = (empty($switches['pid-path']) ? $config->get('application.path.batchPid', '/var/run')
			: $switches['pid-path']);
		$this->pidFile = (empty($switches['pid-file'])
			? preg_replace('/\.php$/', '', basename($_SERVER['argv'][0])) . '.pid'
			: $switches['pid-file']
		);

		if (!is_dir($this->pidPath)) {
			throw new ParameterException('The pid path does not exist');
		}

		if (
			!is_writable($this->pidPath)
			|| (file_exists($this->getFullPidFile()) && !is_writable($this->getFullPidFile()))
		) {
			throw new ParameterException('The pid file is not writable');
		}

		if (isset($switches['help'])) {
			$this->currentUsage = self::HELP_USAGE;
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
		$this->lockFileDescriptor = $fp;
		return true;
	}

	/**
	 * Truncates and releases the lock on the PID file.
	 */
	protected function releaseLock() {
		if ($this->lockFileDescriptor) {
			$fp           = $this->lockFileDescriptor;
			$this->lockFileDescriptor = null;
			ftruncate($fp, 0);
			flock($fp, LOCK_UN);
			fclose($fp);
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
	 *
	 * @param int $signo   The signal number.
	 *
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
	 * Returns the script's decription.
	 *
	 * This method should return a the description for the script. It will be used as the script description in the
	 * help.
	 *
	 * @return string
	 */
	abstract protected function getScriptDescription();

}