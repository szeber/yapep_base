<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Shell
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Shell;


/**
 * Interface for command executors
 *
 * @package    YapepBase
 * @subpackage Shell
 */
interface ICommandExecutor {

	/** Output mode: send command output straight to STDOUT */
	const OUTPUT_STDOUT = 1;
	/** Output mode: return the command's output as a variable */
	const OUTPUT_VAR    = 2;
	/** Output mode: pipe the command's output into a file */
	const OUTPUT_FILE   = 4;

	/** Pipe operator. The output of the left command will be sent as the input of the right command. */
	const OPERATOR_PIPE = '|';
	/** Binary AND operator. The right command will only run if the left command exited with a 0 status code. */
	const OPERATOR_BINARY_AND = '&&';
	/** Binary OR operator. The right command will only run if the left command exited with a non-0 status code. */
	const OPERATOR_BINARY_OR = '||';

	/** Standard output redirection. */
	const OUTPUT_REDIRECT_STDOUT = '>';
	/** Standard error redirection. */
	const OUTPUT_REDIRECT_STDERR = '2>';
	/** Standard output redirection while appending to the target. */
	const OUTPUT_REDIRECT_STDOUT_APPEND = '>>';
	/** Standard error redirection while appendind to the target. */
	const OUTPUT_REDIRECT_STDERR_APPEND = '2>>';

	/** Standard output redirection target. */
	const REDIRECT_TARGET_STDOUT = '&1';
	/** Standard error redirection target. */
	const REDIRECT_TARGET_STDERR = '&2';

	/**
	 * Sets the command.
	 *
	 * @param string $command   The command to be executed.
	 *
	 * @return \YapepBase\Shell\ICommandExecutor   The current instance.
	 */
	public function setCommand($command);

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
	public function setOutputRedirection($outputType, $target, $escapeTarget = true);

	/**
	 * Sets the timeout for the command.
	 *
	 * @param int $timeout   The timeout.
	 *
	 * @return \YapepBase\Shell\ICommandExecutor   The current instance.
	 */
	public function setTimeout($timeout);

	/**
	 * Sets the switch-value separator.
	 *
	 * @param string $separator   The separator.
	 *
	 * @return \YapepBase\Shell\ICommandExecutor   The current instance.
	 */
	public function setSwitchValueSeparator($separator);

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
	public function setOutputMode($mode, $logFile = '');

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
	public function addOutputMode($mode, $logFile = '');

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
	public function addParam($option, $value = null);

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
	public function setChainedCommand(ICommandExecutor $command, $operator);

	/**
	 * Returns the full command.
	 *
	 * @return string
	 *
	 * @throws \YapepBase\Exception\Shell\Exception   If no command is set.
	 */
	public function getCommand();

	/**
	 * Runs the command.
	 *
	 * @return \YapepBase\Shell\CommandOutput   Output of the run.
	 */
	public function run();

}
