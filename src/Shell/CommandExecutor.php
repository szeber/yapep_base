<?php
declare(strict_types=1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Shell;

use YapepBase\Config;
use YapepBase\Exception\ParameterException;
use YapepBase\Exception\Shell\Exception;

/**
 * Class for running external commands.
 */
class CommandExecutor implements ICommandExecutor
{
    /**
     * The command to run.
     *
     * @var string
     */
    protected $command = '';

    /**
     * The parameters for the command
     *
     * @var array
     */
    protected $commandParams = [];

    /**
     * The output mode for the command.
     *
     * @var string
     */
    protected $outputMode = self::OUTPUT_VAR;

    /**
     * Path to the log file. Only used when output mode is set to file. {@uses self::OUTPUT_*}
     *
     * @var string
     */
    protected $logFile = '';

    /**
     * The separator for switch name and value
     *
     * @var string
     */
    protected $switchValueSeparator = ' ';

    /**
     * The timeout for the command.
     *
     * @var int
     */
    protected $timeout;

    /**
     * The chained command that will be run after this one.
     *
     * @var \YapepBase\Shell\ICommandExecutor
     */
    protected $chainedCommand;

    /**
     * The operator for the chained command.
     *
     * @var string
     */
    protected $chainedCommandOperator;

    /**
     * The output redirections.
     *
     * @var array
     */
    protected $outputRedirections = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->timeout = Config::getInstance()->get('system.shell.executedCommandTimeout', 0);
    }

    /**
     * Sets the command.
     *
     * @param string $command   The command to be executed.
     *
     * @return \YapepBase\Shell\ICommandExecutor   The current instance.
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Sets an output redirection to the target.
     *
     * @param string $outputType     The output redirection type. {@uses self::OUTPUT_REDIRECT_*}
     * @param string $target         The target to redirect the output type
     * @param bool   $escapeTarget   Whether the target should be escaped (ie. a file) or not (ie. STDOUT).
     *
     * @return \YapepBase\Shell\ICommandExecutor   The current instance.
     *
     * @throws \YapepBase\Exception\ParameterException   If setting an invalid output redirection type.
     * @throws \YapepBase\Exception\Shell\Exception   If trying to set STDOUT or STDERR redirection and file output is
     *                                                used for the command.
     */
    public function setOutputRedirection($outputType, $target, $escapeTarget = true)
    {
        $validTypes = [
            self::OUTPUT_REDIRECT_STDOUT,
            self::OUTPUT_REDIRECT_STDERR,
            self::OUTPUT_REDIRECT_STDOUT_APPEND,
            self::OUTPUT_REDIRECT_STDERR_APPEND,
        ];
        if (!in_array($outputType, $validTypes)) {
            throw new ParameterException('Invalid output redirection type: ' . $outputType);
        }

        if (
            $outputType == self::OUTPUT_REDIRECT_STDERR
            && isset($this->outputRedirections[self::OUTPUT_REDIRECT_STDERR_APPEND])
        ) {
            unset($this->outputRedirections[self::OUTPUT_REDIRECT_STDERR_APPEND]);
        } elseif (
            $outputType == self::OUTPUT_REDIRECT_STDOUT
            && isset($this->outputRedirections[self::OUTPUT_REDIRECT_STDOUT_APPEND])
        ) {
            unset($this->outputRedirections[self::OUTPUT_REDIRECT_STDOUT_APPEND]);
        } elseif (
            $outputType == self::OUTPUT_REDIRECT_STDERR_APPEND
            && isset($this->outputRedirections[self::OUTPUT_REDIRECT_STDERR])
        ) {
            unset($this->outputRedirections[self::OUTPUT_REDIRECT_STDERR]);
        } elseif (
            $outputType == self::OUTPUT_REDIRECT_STDOUT_APPEND
            && isset($this->outputRedirections[self::OUTPUT_REDIRECT_STDOUT])
        ) {
            unset($this->outputRedirections[self::OUTPUT_REDIRECT_STDOUT]);
        }

        $this->outputRedirections[$outputType] = $escapeTarget ? escapeshellarg($target) : $target;

        return $this;
    }

    /**
     * Sets the timeout for the command.
     *
     * @param int $timeout   The timeout.
     *
     * @return \YapepBase\Shell\ICommandExecutor   The current instance.
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Sets the switch-value separator.
     *
     * @param string $separator   The separator.
     *
     * @return \YapepBase\Shell\ICommandExecutor   The current instance.
     */
    public function setSwitchValueSeparator($separator)
    {
        $this->switchValueSeparator = $separator;

        return $this;
    }

    /**
     * Sets the output mode.
     *
     * @param int    $mode      The output mode. {@uses self::OUTPUT_}
     * @param string $logFile   The log file's path and name in case of file output mode.
     *
     * @return \YapepBase\Shell\ICommandExecutor   The current instance.
     *
     * @throws \YapepBase\Exception\ParameterException   If adding file output mode without specifying a file path.
     * @throws \YapepBase\Exception\Shell\Exception   If STDOUT or STDERR redirection is set for the command with
     *                                                file output.
     */
    public function setOutputMode($mode, $logFile = '')
    {
        if ($this->outputMode & self::OUTPUT_FILE) {
            if (empty($logFile)) {
                throw new ParameterException('File output mode selected and no logfile is specified');
            }

            if (
                !empty($this->outputRedirections[self::OUTPUT_REDIRECT_STDERR])
                || !empty($this->outputRedirections[self::OUTPUT_REDIRECT_STDOUT])
                || !empty($this->outputRedirections[self::OUTPUT_REDIRECT_STDERR_APPEND])
                || !empty($this->outputRedirections[self::OUTPUT_REDIRECT_STDOUT_APPEND])
            ) {
                throw new Exception(
                    'Trying to add file output for the command when STDERR or STDOUT redirection is active'
                );
            }

            $this->logFile = $logFile;
        }
        $this->outputMode = $mode;

        return $this;
    }

    /**
     * Adds a new output mode.
     *
     * @param int    $mode      The output mode. {@uses self::OUTPUT_}
     * @param string $logFile   The log file's path and name in case of file output mode.
     *
     * @return \YapepBase\Shell\ICommandExecutor
     *
     * @throws \YapepBase\Exception\ParameterException   If adding file output mode without specifying a file path.
     * @throws \YapepBase\Exception\Shell\Exception   If STDOUT or STDERR redirection is set for the command with
     *                                                file output.
     */
    public function addOutputMode($mode, $logFile = '')
    {
        if ($mode === self::OUTPUT_FILE) {
            if (empty($logFile)) {
                throw new ParameterException('File output mode selected and no logfile is specified');
            }

            if (
                !empty($this->outputRedirections[self::OUTPUT_REDIRECT_STDERR])
                || !empty($this->outputRedirections[self::OUTPUT_REDIRECT_STDOUT])
                || !empty($this->outputRedirections[self::OUTPUT_REDIRECT_STDERR_APPEND])
                || !empty($this->outputRedirections[self::OUTPUT_REDIRECT_STDOUT_APPEND])
            ) {
                throw new Exception(
                    'Trying to add file output for the command when STDERR or STDOUT redirection is active'
                );
            }

            $this->logFile = $logFile;
        }
        $this->outputMode |= $mode;

        return $this;
    }

    /**
     * Adds a new switch to the command.
     *
     * @param string|null $option   switch.
     * @param string|null $value    value.
     *
     * @return \YapepBase\Shell\ICommandExecutor   The current instance.
     *
     * @throws \YapepBase\Exception\ParameterException   If trying to add an option with neither a name nor a value.
     */
    public function addParam($option, $value = null)
    {
        if (strlen($option) == 0 && strlen($value) == 0) {
            throw new ParameterException('Neither an option name nor a value is provided');
        }
        $this->commandParams[] = [
            'option' => (string)$option,
            'value'  => (string)$value,
        ];

        return $this;
    }

    /**
     * Sets a command that will be chained after the current command with the specified operator.
     *
     * @param ICommandExecutor $command    The command to add.
     * @param string           $operator   The operator to separate the command with. {@uses self::OPERATOR_*}
     *
     * @return \YapepBase\Shell\ICommandExecutor   The current instance.
     *
     * @throws \YapepBase\Exception\ParameterException   If the operator is invalid.
     */
    public function setChainedCommand(ICommandExecutor $command, $operator)
    {
        if (!in_array($operator, [self::OPERATOR_PIPE, self::OPERATOR_BINARY_AND, self::OPERATOR_BINARY_OR])) {
            throw new ParameterException('Invalid operator: ' . $operator);
        }

        $this->chainedCommand         = $command;
        $this->chainedCommandOperator = $operator;

        return $this;
    }

    /**
     * Returns the full command.
     *
     * @return string
     *
     * @throws \YapepBase\Exception\Shell\Exception   If no command is set.
     */
    public function getCommand()
    {
        $command = escapeshellarg($this->command);
        foreach ($this->commandParams as $param) {
            if (strlen($param['option']) > 0) {
                $command .= ' ' . $param['option'];
                if (strlen($param['value']) > 0) {
                    $command .= $this->switchValueSeparator . escapeshellarg($param['value']);
                }
            } else {
                $command .= ' ' . escapeshellarg($param['value']);
            }
        }

        foreach ($this->outputRedirections as $type => $target) {
            $command .= ' ' . $type . ' ' . $target;
        }

        if (!empty($this->chainedCommand)) {
            $command .= ' ' . $this->chainedCommandOperator . ' ' . $this->chainedCommand->getCommand();
        }

        return $command;
    }

    /**
     * Runs the command.
     *
     * @return \YapepBase\Shell\CommandOutput   Output of the run.
     */
    public function run()
    {
        $command = $this->getCommand();

        $originalCommand = $command;

        if (
            empty($this->outputRedirections[self::OUTPUT_REDIRECT_STDERR])
            && empty($this->outputRedirections[self::OUTPUT_REDIRECT_STDERR_APPEND])
        ) {
            // No STDERR redirection is in effect, so redirect STDERR to STDOUT
            $command .= ' 2>&1';
        }

        if (!empty($this->timeout)) {
            $command = 'timeout ' . (int)$this->timeout . ' ' . $command;
        }

        if ($this->outputMode & self::OUTPUT_FILE && $this->logFile) {
            // Pipe output into the specified logfile
            $command .= ' | head -c 4000000 | tee -a ' . escapeshellarg($this->logFile);
            file_put_contents($this->logFile, PHP_EOL . str_repeat('*', 40) . PHP_EOL . 'Original command: '
                . $originalCommand . PHP_EOL . PHP_EOL, FILE_APPEND);
        }

        $output = [];
        $code   = 0;

        exec($command, $output, $code);

        return new CommandOutput($originalCommand, implode(PHP_EOL, $output), $code, $command);
    }
}
