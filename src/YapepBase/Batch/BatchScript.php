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
declare(ticks = 1);

namespace YapepBase\Batch;

use YapepBase\Application;
use YapepBase\DependencyInjection\SystemContainer;
use YapepBase\Event\Event;
use YapepBase\Mime\MimeType;
use YapepBase\View\ViewDo;

/**
 * Base class for batch scripts.
 *
 * Handles event dispatching for application start and finish, and handles unhandled exceptions.
 *
 * @package    YapepBase
 * @subpackage Batch
 */
abstract class BatchScript {

	/** Help usage. */
	const HELP_USAGE = 'help';

	/**
	 * Stores the content type used by the script for output.
	 *
	 * @var string
	 */
	protected $contentType = MimeType::PLAINTEXT;

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
		$this->setSignalHandler();

		if (function_exists('pcntl_signal')) {
			pcntl_signal(SIGTERM, array(&$this, 'handleSignal'), true);
			pcntl_signal(SIGHUP, array(&$this, 'handleSignal'), true);
			pcntl_signal(SIGINT, array(&$this, 'handleSignal'), true);
		}
	}

	/**
	 * Executes the script.
	 *
	 * @return void
	 */
	abstract protected function execute();

	/**
	 * This function is called, if the process receives an interrupt, term signal, etc. It can be used to clean up
	 * stuff. Note, that this function is not guaranteed to run or it may run after execution.
	 *
	 * @return void
	 */
	abstract protected function abort();

	/**
	 * Returns the script's description.
	 *
	 * This method should return a the description for the script. It will be used as the script description in the
	 * help.
	 *
	 * @return string
	 */
	abstract protected function getScriptDescription();

	/**
	 * Called before the run process.
	 *
	 * Please be cautious! In case of overriding, don't forget to call this method first.
	 *
	 * @return void
	 */
	protected function runBefore() {
		$application = Application::getInstance();
		$container = $application->getDiContainer();
		$container[SystemContainer::KEY_VIEW_DO] = $container->share(function($container) {
			return new ViewDo(MimeType::PLAINTEXT);
		});

	}

	/**
	 * Called after the run process.
	 *
	 * Please be cautious! In case of overriding, don't forget to call this method in the end.
	 *
	 * @return void
	 */
	protected function runAfter() {
		// noop
	}

	/**
	 * Starts script execution.
	 *
	 * @return void
	 *
	 * @throws \Exception   On errors
	 */
	public function run() {
		$eventHandlerRegistry = Application::getInstance()->getDiContainer()->getEventHandlerRegistry();
		$eventHandlerRegistry->raise(new Event(Event::TYPE_APPSTART));

		$this->prepareSwitches();
		try {
			$this->parseSwitches($this->cliHelper->getParsedArgs());
		}
		catch (\Exception $exception) {
			$this->cliHelper->setErrorMessage($exception->getMessage());

			echo $this->cliHelper->getUsageOutput(false);
			// Re-throw the exception
			throw $exception;
		}

		$this->runBefore();
		try {
			if ($this->currentUsage == self::HELP_USAGE) {
				echo $this->cliHelper->getUsageOutput(true);
			}
			else {
				$this->execute();
			}
		}
		catch (\Exception $exception) {
			$this->removeSignalHandler();
			Application::getInstance()->getDiContainer()->getErrorHandlerRegistry()->handleException($exception);
		}
		$this->removeSignalHandler();

		$this->runAfter();
		$eventHandlerRegistry->raise(new Event(Event::TYPE_APPFINISH));
	}

	/**
	 * Sets the switches used by the script
	 *
	 * @return void
	 */
	protected function prepareSwitches() {
		$this->usageIndexes[self::HELP_USAGE] = $this->cliHelper->addUsage('Help');

		$this->cliHelper->addSwitch(null, 'help', 'Shows the help', $this->usageIndexes[self::HELP_USAGE]);
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
		if (isset($switches['help'])) {
			$this->currentUsage = self::HELP_USAGE;
		}
	}

	/**
	 * Sets the signal handlers
	 *
	 * @return void
	 */
	protected function setSignalHandler() {
		$this->handleSignals = true;
	}

	/**
	 * Removes the signal handlers
	 *
	 * @return void
	 */
	protected function removeSignalHandler() {
		$this->handleSignals = false;
	}

	/**
	 * Signal handler
	 *
	 * @param int $signal   The signal number.
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	final public function handleSignal($signal) {
		if ($this->handleSignals) {
			switch ($signal) {
				case SIGTERM:
				case SIGHUP:
				case SIGINT:
					$this->runBeforeAbort();
					$this->abort();
					break;
			}
		}
	}

	/**
	 * Runs before the abort() method called.
	 *
	 * Can be handy if you want to implement some logging.
	 *
	 * @return void
	 */
	protected function runBeforeAbort() {
		// noop
	}

	/**
	 * Stores one ore more value(s).
	 *
	 * @param string $nameOrData   The name of the key, or the storable data in an associative array.
	 * @param mixed  $value        The value.
	 *
	 * @throws \Exception   If the key already exist.
	 *
	 * @return void
	 */
	protected function setToView($nameOrData, $value = null) {
		Application::getInstance()->getDiContainer()->getViewDo()->set($nameOrData, $value);
	}

	/**
	 * Translates the specified string.
	 *
	 * @param string $string       The string.
	 * @param array  $parameters   The parameters for the translation.
	 * @param string $language     The language.
	 *
	 * @return string
	 */
	protected function _($string, $parameters = array(), $language = null) {
		return Application::getInstance()->getI18nTranslator()->translate(get_class($this), $string, $parameters,
			$language);
	}
}