<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Mock\Validator
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Mock\Validator;


/**
 * Mock object to be able to test the Validator abstract class.
 *
 * @codeCoverageIgnore
 */
class MockValidator extends \YapepBase\Validator\ValidatorAbstract {

	/**
	 * Checks the given value as a string.
	 *
	 * Provides the basic validation methods for string parameters.
	 *
	 * @param string $value            The value to check.
	 * @param bool   $isEmptyAllowed   If TRUE the given value wont be checked for emptiness.
	 * @param int    $minLength        If given the given value will be checked if it is longer than the given value.
	 * @param int    $maxLength        If given the given value will be checked if it is shorter than the given value.
	 * @param string $regExp           A regular expression. If given the given value will be checked with this regexp.
	 *
	 * @throws \YapepBase\Exception\Validator\Exception   If the given value does not meet the given requirements.
	 *
	 * @return void.
	 */
	public function checkString($value, $isEmptyAllowed = false,
		$minLength = null, $maxLength = null, $regExp = null) {

		parent::checkString($value, $isEmptyAllowed, $minLength, $maxLength, $regExp);
	}

	/**
	 * Checks the given value as a string (Uses the default parameters of the checkString() method).
	 *
	 * Provides the basic validation methods for string parameters.
	 *
	 * @param string $value   The value to check.
	 *
	 * @throws \YapepBase\Exception\Validator\Exception   If the given value does not meet the given requirements.
	 *
	 * @return void.
	 */
	public function checkStringWithDefaultParams($value) {
		parent::checkString($value);
	}
}