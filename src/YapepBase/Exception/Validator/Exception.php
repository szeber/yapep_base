<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Exception\Validator
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Exception\Validator;


/**
 * Base Exception class for validations.
 *
 * @package    YapepBase
 * @subpackage Exception\Validator
 */
class Exception extends \YapepBase\Exception\Exception {

	/** Error Code given when a checked value is empty. */
	const EC_EMPTY = 0;

	/** Error Code given when a checked value violates a unique constraint. */
	const EC_NOT_UNIQUE = 1;

	/** Error Code given when a checked value is too short. */
	const EC_SHORT = 2;

	/** Error Code given when a checked value is too long. */
	const EC_LONG = 3;

	/** Error Code given when a checked value contains unacceptable characters. */
	const EC_INVALID_CHARS = 4;

	/** Error Code given when a checked value does not contain some characters it should. */
	const EC_REQUIRED_CHARS_MISSING = 5;

	/**
	 * Constructor.
	 *
	 * @param int    $code      The code of the error. {@uses self::EC_*}
	 * @param string $message   The message of the error.
	 */
	public function __construct($code, $message = '') {
		$this->code = $code;
		$this->message = $message;
	}
}