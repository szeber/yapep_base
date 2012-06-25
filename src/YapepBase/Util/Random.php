<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Util
 * @author       Janos Pasztor <janos@janoszen.hu>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Util;
use YapepBase\Exception\Exception;

/**
 * Random class
 *
 * @package    YapepBase
 * @subpackage Util
 */
class Random {
	/**
	 * Automatically picks a random number generation method.
	 */
	const NUMBER_METHOD_AUTO = 'auto';

	/**
	 * Generates a random number using the rand() method.
	 */
	const NUMBER_METHOD_RAND = 'rand';

	/**
	 * Generates a random number using the mtrand() method.
	 */
	const NUMBER_METHOD_MTRAND = 'mtrand';

	/**
	 * Automatically picks a random string generation method.
	 */
	const STRING_METHOD_AUTO = 'auto';

	/**
	 * Generates a random string using time() and md5()
	 */
	const STRING_METHOD_TIME = 'time';

	/**
	 * Generates a random string using the uniquid() method.
	 */
	const STRING_METHOD_UNIQID = 'uniqid';

	/**
	 * Generates a random string using the openssl_random_pseudo_bytes() method.
	 */
	const STRING_METHOD_OPENSSL = 'openssl';

	/**
	 * Generate a pseudo-random number between $min and $max. If possible, this function will use mt_rand() for
	 * random number generation. Otherwise it will fall back to rand(), which uses the libc random number generator.
	 *
	 * @param float|int $min      The minimum value.
	 * @param float|int $max      The maximum value.
	 * @param string    $method   Random generator method to use. If not available, fallback to auto.
	 *
	 * @return float
	 *
	 * @throws \YapepBase\Exception\Exception
	 */
	public static function getPseudoNumber($min = 0, $max = 1, $method = self::NUMBER_METHOD_AUTO) {
		switch ($method) {
			case self::NUMBER_METHOD_AUTO :
			case self::NUMBER_METHOD_MTRAND :
				if (\function_exists('\mt_rand')) {
					$value =\mt_rand();
					$maxvalue =\mt_getrandmax();
					break;
				}

			case self::NUMBER_METHOD_RAND :
				$value =\rand();
				$maxvalue =\getrandmax();
				break;

			default :
				throw new Exception('Invalid method: ' . $method);
				break;
		}
		return ((float)$min + $value * (((float)$max - (float)$min) / $maxvalue));
	}

	/**
	 * Generate a pseudo-random string in the given length. This string is NOT suitable for cryptographic purposes!
	 *
	 * @param integer $length   The length.
	 * @param string  $method   The method to use for random string generation. Falls back to default if the
	 *                          method is not available.
	 *
	 * @return string
	 *
	 * @throws \YapepBase\Exception\Exception   If the method is invalid
	 */
	public static function getPseudoString($length = 23, $method = self::STRING_METHOD_AUTO) {
		$string = '';
		switch ($method) {
			case self::STRING_METHOD_AUTO :
			case self::STRING_METHOD_OPENSSL :
				if (\function_exists('\openssl_random_pseudo_bytes')) {
					while (\strlen($string) < $length) {
						$string .= \sha1(\openssl_random_pseudo_bytes(20, $strong));
					}
					$string =\substr($string, 0, $length);
					break;
				}

			case self::STRING_METHOD_UNIQID :
				while (\strlen($string) < $length) {
					$string .= \str_replace('.', '', \uniqid('', true));
				}
				$string =\substr($string, 0, $length);
				break;

			case self::STRING_METHOD_TIME :
				while (\strlen($string) < $length) {
					$string .=\md5(\time());
				}
				$string =\substr($string, 0, $length);
				break;

			default :
				throw new Exception('Invalid method: ' . $method);
				break;
		}
		return $string;
	}
}