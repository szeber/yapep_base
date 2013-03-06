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

/**
 * Result of a shell command that has been run.
 *
 * @package      YapepBase
 * @subpackage   Shell
 */
class CommandOutput {

	/** @var string       The command that was run. */
	public $command = '';
	/** @var string       Output of the command if returning the output is enabled. */
	public $output = '';
	/** @var string|int   The return code of the command. */
	public $code = '';

	/**
	 * Constructor.
	 *
	 * @param string $command   The command that was run.
	 * @param string $output    Output of the command.
	 * @param string $code      The return code of the command.
	 */
	public function __construct($command = '', $output = '', $code = '') {
		$this->command = $command;
		$this->output  = $output;
		$this->code    = $code;
	}

	/**
	 * Returns TRUE if the command was successful (returned a code of 0).
	 *
	 * @return bool
	 */
	public function isSuccessful() {
		return $this->code == '0';
	}
}
