<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Test\Util
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Util;

/**
 * RandomTest class
 *
 * @package    YapepBase
 * @subpackage Test\Util
 * @covers \YapepBase\Util\Random
 */
use YapepBase\Exception\Exception;

use YapepBase\Util\Random;

class RandomTest extends \YapepBase\BaseTest {

	/**
	 * Tests using the \mt_rand() function
	 */
	public function testGetPseudoNumberMT() {
		if (!\function_exists('\mt_rand')) {
			$this->markTestSkipped('\mt_rand() is not available');
		}
		$number = Random::getPseudoNumber(0, 1, Random::NUMBER_METHOD_MTRAND);
		$this->assertEquals('double', \gettype($number));
		$this->assertGreaterThanOrEqual(0, $number);
		$this->assertLessThanOrEqual(1, $number);
	}

	/**
	 * Tests using the \rand() function
	 */
	public function testGetPseudoNumber() {
		$number = Random::getPseudoNumber(0, 1, Random::NUMBER_METHOD_RAND);
		$this->assertEquals('double', \gettype($number));
		$this->assertGreaterThanOrEqual(0, $number);
		$this->assertLessThanOrEqual(1, $number);
	}

	/**
	 * Tests the method check
	 */
	public function testGetPseudoNumberMethodChecks() {
		try {
			Random::getPseudoNumber(0, 1, 'nonexistent');
			$this->fail('The method check fails');
		} catch (Exception $exception) {}
	}

	/**
	 * Tests using the \openssl_random_pseudo_bytes() function
	 */
	public function testGetPseudoStringOpenSSL() {
		if (!\function_exists('\openssl_random_pseudo_bytes')) {
			$this->markTestSkipped('\openssl_random_pseudo_bytes() is not available');
		}
		$string = Random::getPseudoString(61, Random::STRING_METHOD_OPENSSL);
		$this->assertRegExp('/^[-_a-zA-Z0-9]{61}$/', $string);
	}

	/**
	 * Tests using the \uniqid() function
	 */
	public function testGetPseudoStringUniqid() {
		$string = Random::getPseudoString(61, Random::STRING_METHOD_UNIQID);
		$this->assertRegExp('/^[-_a-zA-Z0-9]{61}$/', $string);
	}

	/**
	 * Tests using the \md5(time()) functions
	 */
	public function testGetPseudoStringTime() {
		$string = Random::getPseudoString(61, Random::STRING_METHOD_TIME);
		$this->assertRegExp('/^[-_a-zA-Z0-9]{61}$/', $string);
	}

	/**
	 * Tests the method check
	 */
	public function testGetPseudoStringMethodChecks() {
		try {
			Random::getPseudoString(61, 'nonexistent');
			$this->fail('The method check fails');
		} catch (Exception $exception) {}
	}
}