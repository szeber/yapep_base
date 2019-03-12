<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

// Set the ticks, so we can handle signals
declare(ticks = 1);

namespace YapepBase\Batch;

use YapepBase\Application;
use YapepBase\DependencyInjection\Container;
use YapepBase\ErrorHandler\ITerminatable;
use YapepBase\Event\Event;
use YapepBase\Exception\ParameterException;
use YapepBase\Mime\MimeType;
use YapepBase\View\ViewDo;

/**
 * Base class for batch scripts.
 *
 * Handles event dispatching for application start and finish, and handles unhandled exceptions.
 *
 * Setting the exit code for the script is possible. When setting it, the PHP execution environment's exit codes are
 * not allowed, so the exit code is distinguishable from a PHP execution error. Custom exit codes should be above 100.
 */
abstract class BatchScript implements ITerminatable
{
    /** Help usage. */
    const HELP_USAGE = 'help';

    /** Exit code for successful execution */
    const EXIT_CODE_SUCCESS = 0;

    /** Exit code that PHP outputs if the specified source file is not found. */
    const EXIT_CODE_PHP_FILE_NOT_FOUND = 1;

    /**
     * Exit code that PHP outputs if the execution was aborted because of a fatal error
     * (E_ERROR, unhandled exception, parse errors, etc).
     */
    const EXIT_CODE_PHP_FATAL_ERROR = 255;

    /** Exit code for invalid invocation (switch or parameter errors). */
    const EXIT_CODE_INVOCATION_ERROR = 10;

    /** Exit code for runtime errors. */
    const EXIT_CODE_RUNTIME_ERROR = 20;

    /** Exit code for fatal errors. */
    const EXIT_CODE_FATAL_ERROR = 30;

    /** Exit code for unhandled exceptions. */
    const EXIT_CODE_UNHANDLED_EXCEPTION = 31;

    /** Exit code for aborted runs because of a signal. */
    const EXIT_CODE_SIGNAL_ABORT = 40;

    /** Exit code for failed locking errors. */
    const EXIT_CODE_LOCK_FAILED = 50;

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
    protected $usageIndexes = [];

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
     * The exit code for the execution.
     *
     * @var int
     */
    private $exitCode;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setExitCode(self::EXIT_CODE_SUCCESS);
        $this->cliHelper = new CliUserInterfaceHelper($this->getScriptDescription());
        $this->setSignalHandler();

        if (function_exists('pcntl_signal')) {
            pcntl_signal(SIGTERM, [&$this, 'handleSignal'], true);
            pcntl_signal(SIGHUP, [&$this, 'handleSignal'], true);
            pcntl_signal(SIGINT, [&$this, 'handleSignal'], true);
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
    protected function runBefore()
    {
        $application                       = Application::getInstance();
        $container                         = $application->getDiContainer();
        $container[Container::KEY_VIEW_DO] = $container->share(function ($container) {
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
    protected function runAfter()
    {
        // noop
    }

    /**
     * Sets the current instance as the terminator object.
     *
     * @return void
     */
    protected function setAsTerminator()
    {
        Application::getInstance()->getDiContainer()->getErrorHandlerRegistry()->setTerminator($this);
    }

    /**
     * Sets the exit code for the batch script
     *
     * @param int $exitCode   The exit code to set.
     *
     * @return void
     *
     * @throws \YapepBase\Exception\ParameterException   If a PHP specific exit code is used.
     */
    protected function setExitCode($exitCode)
    {
        if (in_array($exitCode, [self::EXIT_CODE_PHP_FILE_NOT_FOUND, self::EXIT_CODE_PHP_FATAL_ERROR])) {
            throw new ParameterException('Setting PHP specific exit codes is not allowed: ' . $exitCode);
        }
        $this->exitCode = $exitCode;
    }

    /**
     * Returns the currently set exit code.
     *
     * @return int
     */
    protected function getExitCode()
    {
        return $this->exitCode;
    }

    /**
     * Starts script execution.
     *
     * @return void
     *
     * @throws \Exception   On errors
     */
    public function run()
    {
        $this->setAsTerminator();
        $eventHandlerRegistry = Application::getInstance()->getDiContainer()->getEventHandlerRegistry();
        $eventHandlerRegistry->raise(new Event(Event::TYPE_APPLICATION_BEFORE_RUN));

        $this->prepareSwitches();

        try {
            $this->parseSwitches($this->cliHelper->getParsedArgs());
        } catch (\Exception $exception) {
            $this->cliHelper->setErrorMessage($exception->getMessage());

            // Set the exit code to invocation error if it's currently set to success.
            if ($this->getExitCode() == self::EXIT_CODE_SUCCESS) {
                $this->setExitCode(self::EXIT_CODE_INVOCATION_ERROR);
            }

            echo $this->cliHelper->getUsageOutput(false);
            // Re-throw the exception
            throw $exception;
        }

        $this->runBefore();

        try {
            if ($this->currentUsage == self::HELP_USAGE) {
                echo $this->cliHelper->getUsageOutput(true);
            } else {
                $eventHandlerRegistry->raise(new Event(Event::TYPE_CONTROLLER_BEFORE_ACTION));
                $this->execute();
                $eventHandlerRegistry->raise(new Event(Event::TYPE_CONTROLLER_AFTER_ACTION));
            }
        } catch (\Exception $exception) {
            $this->removeSignalHandler();
            if (self::EXIT_CODE_SUCCESS == $this->getExitCode()) {
                $this->setExitCode(self::EXIT_CODE_UNHANDLED_EXCEPTION);
            }
            Application::getInstance()->getDiContainer()->getErrorHandlerRegistry()->handleException($exception);
        }
        $this->removeSignalHandler();

        $this->runAfter();
        $eventHandlerRegistry->raise(new Event(Event::TYPE_APPLICATION_AFTER_RUN));
    }

    /**
     * Sets the switches used by the script
     *
     * @return void
     */
    protected function prepareSwitches()
    {
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
    protected function parseSwitches(array $switches)
    {
        if (isset($switches['help'])) {
            $this->currentUsage = self::HELP_USAGE;
        }
    }

    /**
     * Sets the signal handlers
     *
     * @return void
     */
    protected function setSignalHandler()
    {
        $this->handleSignals = true;
    }

    /**
     * Removes the signal handlers
     *
     * @return void
     */
    protected function removeSignalHandler()
    {
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
    final public function handleSignal($signal)
    {
        if ($this->handleSignals) {
            switch ($signal) {
                case SIGTERM:
                case SIGHUP:
                case SIGINT:
                    $this->setExitCode(self::EXIT_CODE_SIGNAL_ABORT);
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
    protected function runBeforeAbort()
    {
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
    protected function setToView($nameOrData, $value = null)
    {
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
    protected function _($string, $parameters = [], $language = null)
    {
        return Application::getInstance()->getI18nTranslator()->translate(
            get_class($this),
            $string,
            $parameters,
            $language
        );
    }

    /**
     * Called just before the application exits.
     *
     * @param bool $isFatalError   TRUE if the termination is because of a fatal error.
     *
     * @return void
     */
    public function terminate($isFatalError)
    {
        if ($isFatalError && self::EXIT_CODE_SUCCESS == $this->getExitCode()) {
            $this->setExitCode(self::EXIT_CODE_FATAL_ERROR);
        }

        exit($this->getExitCode());
    }
}
