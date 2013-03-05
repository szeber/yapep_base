<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Exception\Shell
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Exception\Shell;


/**
 * Base Exception class for the Shell classes
 *
 * @package    YapepBase
 * @subpackage Exception\Shell
 */
class Exception extends \YapepBase\Exception\Exception {

	/** Error code for failed process creation. */
	const ERR_PROCESS_CREATION_FAILED = 1001;
	/** Error code for the command running beyond the timeout, and terminating gracefully. */
	const ERR_TIMEOUT_REACHED_TERMINATED = 1002;
	/** Error code for the command running beyond the timeout and failing to terminate gracefully. */
	const ERR_TIMEOUT_REACHED_KILLED = 1003;

	/**
	 * The status code of the command
	 *
	 * @var int
	 */
	protected $statusCode;

	/**
	 * The (partial) output of the command.
	 *
	 * @var string
	 */
	protected $output;

	/**
	 * Constructor
	 *
	 * @param string    $message      The message string.
	 * @param int       $code         The exception code.
	 * @param string    $output       The (partial) output of the command.
	 * @param int       $statusCode   The status code for the command.
	 * @param Exception $previous     The previous exception.
	 */
	public function __construct(
		$message = "", $code = 0, $output = null, $statusCode = null, Exception $previous = null
	) {
		parent::__construct($message, $code, $previous);

		$this->output     = $output;
		$this->statusCode = $statusCode;
	}

	/**
	 * Returns the (partial) output for the command if available.
	 *
	 * @return string
	 */
	public function getOutput() {
		return $this->output;
	}

	/**
	 * Returns the status code for the command if available.
	 *
	 * @return int
	 */
	public function getStatusCode() {
		return $this->statusCode;
	}


}