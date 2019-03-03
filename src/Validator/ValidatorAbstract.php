<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Validator
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Validator;


use YapepBase\Exception\Validator\Exception as ValidatorException;

/**
 * Base validator every validator should extend.
 *
 * @package    YapepBase
 * @subpackage Validator
 */
abstract class ValidatorAbstract {

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
	protected function checkString($value, $isEmptyAllowed = false,
		$minLength = null, $maxLength = null, $regExp = null) {

		if (!$isEmptyAllowed && empty($value)) {
			throw new ValidatorException(ValidatorException::EC_EMPTY);
		}

		if (!empty($minLength) && mb_strlen($value) < $minLength) {
			throw new ValidatorException(ValidatorException::EC_SHORT);
		}

		if (!empty($maxLength) && mb_strlen($value) > $maxLength) {
			throw new ValidatorException(ValidatorException::EC_LONG);
		}

		if (!empty($regExp) && !preg_match($regExp, $value)) {
			throw new ValidatorException(ValidatorException::EC_INVALID_CHARS);
		}
	}
}
