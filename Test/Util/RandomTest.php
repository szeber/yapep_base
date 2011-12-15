<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Test\Util
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Test\Util;

/**
 * RandomTest class
 *
 * @package    YapepBase
 * @subpackage Test\Util
 * @covers \YapepBase\Util\Random
 */
use YapepBase\Exception\Exception;

use YapepBase\Util\Random;

class RandomTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Tests using the \mt_rand() function
	 */
	public function testPseudoNumberMT() {
		if (!\function_exists('\mt_rand')) {
			$this->markTestSkipped('\mt_rand() is not available');
		}
		$number = Random::pseudoNumber(0, 1, Random::NUMBER_METHOD_MTRAND);
		$this->assertEquals('double', \gettype($number));
		$this->assertGreaterThanOrEqual(0, $number);
		$this->assertLessThanOrEqual(1, $number);
	}

	/**
	 * Tests using the \rand() function
	 */
	public function testPseudoNumber() {
		$number = Random::pseudoNumber(0, 1, Random::NUMBER_METHOD_RAND);
		$this->assertEquals('double', \gettype($number));
		$this->assertGreaterThanOrEqual(0, $number);
		$this->assertLessThanOrEqual(1, $number);
	}

	/**
	 * Tests the method check
	 */
	public function testPseudoNumberMethodChecks() {
	    try {
	        Random::pseudoNumber(0, 1, 'nonexistent');
            $this->fail('The method check fails');
	    } catch (Exception $exception) {}
	}

	/**
	 * Tests using the \openssl_random_pseudo_bytes() function
	 */
	public function testPseudoStringOpenSSL() {
		if (!\function_exists('\openssl_random_pseudo_bytes')) {
			$this->markTestSkipped('\openssl_random_pseudo_bytes() is not available');
		}
		$string = Random::pseudoString(61, Random::STRING_METHOD_OPENSSL);
		$this->assertEquals(61, \strlen($string));
	}

	/**
	 * Tests using the \uniqid() function
	 */
	public function testPseudoStringUniqid() {
		$string = Random::pseudoString(61, Random::STRING_METHOD_UNIQID);
		$this->assertEquals(61, \strlen($string));
	}

	/**
	 * Tests using the \md5(time()) functions
	 */
	public function testPseudoStringTime() {
		$string = Random::pseudoString(61, Random::STRING_METHOD_TIME);
		$this->assertEquals(61, \strlen($string));
	}

	/**
	 * Tests the method check
	 */
	public function testPseudoStringMethodChecks() {
	    try {
	        Random::pseudoString(61, 'nonexistent');
            $this->fail('The method check fails');
	    } catch (Exception $exception) {}
	}

}