<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Shell
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Shell;
use YapepBase\Config;
use YapepBase\Exception\Shell\Exception;
use YapepBase\Exception\ParameterException;

/**
 * Class for running external commands.
 *
 * @package    YapepBase
 * @subpackage Shell
 */
class CommandExecutor implements ICommandExecutor {

	/** ID of the STDIN pipe */
	const PIPE_STDIN = 0;
	/** ID of the STDOUT pipe */
	const PIPE_STDOUT = 1;
	/** ID of the STDERR pipe */
	const PIPE_STDERR = 2;

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
	protected $commandParams = array();

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
	 * Constructor.
	 */
	public function __construct() {
		$this->timeout = Config::getInstance()->get('system.shell.executedCommandTimeout', 0);
	}

	/**
	 * Sets the command.
	 *
	 * @param string $command   The command to be executed.
	 *
	 * @return \YapepBase\Shell\ICommandExecutor   The current instance.
	 */
	public function setCommand($command) {
		$this->command = $command;
		return $this;
	}



	/**
	 * Sets the timeout for the command.
	 *
	 * @param int $timeout   The timeout.
	 *
	 * @return \YapepBase\Shell\ICommandExecutor   The current instance.
	 */
	public function setTimeout($timeout) {
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
	public function setSwitchValueSeparator($separator) {
		$this->switchValueSeparator = $separator;
		return $this;
	}

	/**
	 * Sets the output mode
	 *
	 * @param int    $mode      The output mode. {@uses self::OUTPUT_}
	 * @param string $logFile   The log file's path and name in case of file output mode.
	 *
	 * @return \YapepBase\Shell\ICommandExecutor   The current instance.
	 *
	 * @throws \YapepBase\Exception\ParameterException   If adding file output mode without specifying a file path.
	 */
	public function setOutputMode($mode, $logFile = '') {
		if ($this->outputMode & self::OUTPUT_FILE) {
			if (empty($logFile)) {
				throw new ParameterException('File output mode selected and no logfile is specified');
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
	 * @return \YapepBase\Shell\ICommandExecutor   The current instance.
	 *
	 * @throws \YapepBase\Exception\ParameterException   If adding file output mode without specifying a file path.
	 */
	public function addOutputMode($mode, $logFile = '') {
		if ($mode === self::OUTPUT_FILE) {
			if (empty($logFile)) {
				throw new ParameterException('File output mode selected and no logfile is specified');
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
	public function addParam($option, $value = null) {
		if (strlen($option) == 0 && strlen($value) == 0) {
			throw new ParameterException('Neither an option name nor a value is provided');
		}
		$this->commandParams[] = array(
			'option' => (string)$option,
			'value'  => (string)$value,
		);
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
	public function setChainedCommand(ICommandExecutor $command, $operator) {
		if (!in_array($operator, array(self::OPERATOR_PIPE, self::OPERATOR_BINARY_AND, self::OPERATOR_BINARY_OR))) {
			throw new ParameterException('Invalid operator: ' . $operator);
		}

		$this->chainedCommand = $command;
		$this->chainedCommandOperator = $operator;
		return $this;
	}

	/**
	 * Returns the full command.
	 *
	 * @return string
	 */
	public function getCommand() {
		$command = escapeshellarg($this->command);
		foreach ($this->commandParams as $param) {
			if (strlen($param['option']) > 0) {
				$command .= ' ' . $param['option'];
				if (strlen($param['value']) > 0) {
					$command .= $this->switchValueSeparator . escapeshellarg($param['value']);
				}
			}
			else {
				$command .= ' ' . escapeshellarg($param['value']);
			}
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
	 *
	 * @throws \YapepBase\Exception\Shell\Exception   If there was an error while opening the process
	 *                                                or in case of a timeout.
	 */
	public function run() {
		$descriptorSpec = array(
			self::PIPE_STDIN  => array('pipe', 'r'),
			self::PIPE_STDOUT => array('pipe', 'w'),
			self::PIPE_STDERR => array('pipe', 'w')
		);

		$sigTermSent = false;
		$pipes = array();

		$command = $this->getCommand();

		$originalCommand = $command;

		if ($this->outputMode & self::OUTPUT_FILE && $this->logFile) {
			// Redirect STDERR into STDOUT and pipe that into the specified logfile.
			$command .= ' 2>&1 | head -c 4000000 | tee -a ' . $this->logFile;
			file_put_contents($this->logFile, PHP_EOL . str_repeat('*', 40) . PHP_EOL . 'Original command: '
				. $originalCommand . PHP_EOL . PHP_EOL, FILE_APPEND);
		}

		$process = proc_open($command, $descriptorSpec, $pipes);

		if (!is_resource($process)) {
			throw new Exception('Failed to open process with command: ' . $command,
				Exception::ERR_PROCESS_CREATION_FAILED);
		}

		// No data is sent to the process's STDIN, so close it.
		fclose($pipes[self::PIPE_STDIN]);

		// Both STDOUT and STDERR should be non-blocking.
		stream_set_blocking($pipes[self::PIPE_STDOUT], false);
		stream_set_blocking($pipes[self::PIPE_STDERR], false);

		$output = '';

		if ($this->timeout > 0) {
			$stopTime = time() + $this->timeout;
		}
		else {
			$stopTime = 0;
		}

		while (true) {
			$read = array();

			if (!feof($pipes[self::PIPE_STDOUT])) {
				$read[] = $pipes[self::PIPE_STDOUT];
			}

			if (!feof($pipes[self::PIPE_STDERR])) {
				$read[] = $pipes[self::PIPE_STDERR];
			}

			if (!$read) {
				break;
			}

			if ($stopTime > 0 && $stopTime < time()) {
				// We have exceeded the timeout.
				if ($sigTermSent) {
					// We have already tried to wait for the command to terminate normally, so kill it
					proc_terminate($process, SIGKILL);
					// Close the pipes
					fclose($pipes[self::PIPE_STDOUT]);
					fclose($pipes[self::PIPE_STDERR]);
					// Close the process
					proc_close($process);

					throw new Exception('Killed command for exceeding timeout and failing to terminate gracefully. '
						. 'Command: ' . $originalCommand, Exception::ERR_TIMEOUT_REACHED_KILLED,
						$this->outputMode & self::OUTPUT_VAR ? $output : null);
				}
				else {
					$sigTermSent = true;
					// Add 2 seconds for the process to terminate gracefully.
					$stopTime += 2;
					proc_terminate($process, SIGTERM);
				}
			}

			$write = array();
			$ex = array();

			$ready = stream_select($read, $write, $ex, 1);

			if ($ready === false) {
				break;
			}

			foreach ($read as $r) {
				$s = fread($r, 1024);
				if ($this->outputMode & self::OUTPUT_VAR) {
					$output .= $s;
				}
				if ($this->outputMode & self::OUTPUT_STDOUT) {
					echo $s;
				}
			}
		}

		fclose($pipes[self::PIPE_STDOUT]);
		fclose($pipes[self::PIPE_STDERR]);

		$status = proc_get_status($process);
		$exitCode = proc_close($process);

		$code = ($status['running'] ? $exitCode : $status['exitcode']);

		if ($sigTermSent) {
			proc_close($process);
			throw new Exception('Gracefully terminated command for exceeding timeout. Command: ' . $originalCommand,
				Exception::ERR_TIMEOUT_REACHED_TERMINATED, $this->outputMode & self::OUTPUT_VAR ? $output : null,
				$status);
		}

		return new CommandOutput($originalCommand, $output, $code);
	}
}
