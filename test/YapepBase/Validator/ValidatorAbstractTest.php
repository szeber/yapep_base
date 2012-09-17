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

use \PHPUnit_Framework_AssertionFailedError;

use YapepBase\Exception\Validator\Exception as ValidatorException;
use YapepBase\Mock\Validator\MockValidator;

/**
 * Test case for testing the BoAbstract class
 *
 * @package    YapepBase
 * @subpackage Validator
 */
class ValidatorAbstractTest extends \PHPUnit_Framework_TestCase {

	/**
	 * The mock validator object.
	 *
	 * @var MockValidator
	 */
	protected $mockValidator;

	/**
	 * Runs before each test
	 *
	 * @return void
	 */
	protected function setUp() {
		parent::setUp();

		$this->mockValidator = new MockValidator();
	}

	/**
	 * Runs after each test
	 *
	 * @return void
	 */
	protected function tearDown() {
		parent::tearDown();
	}

	/**
	 * Tests the checkString() method.
	 *
	 * @return void
	 */
	public function testCheckString() {
		$testString = 'abcd1234-=';

		$testCases = array(
			// Check the string with default params
			array(
				'method' => array($this->mockValidator, 'checkStringWithDefaultParams'),
				'params' => array($testString),
			),
			// Check an empty string with default params
			array(
				'method'    => array($this->mockValidator, 'checkStringWithDefaultParams'),
				'params'    => array(''),
				'errorCode' => ValidatorException::EC_EMPTY,
			),
			// Check when the minimum length is more then the length of the string
			array(
				'method'    => array($this->mockValidator, 'checkString'),
				'params'    => array($testString, false, 29),
				'errorCode' => ValidatorException::EC_SHORT,
			),
			// Check when the maximum length is less then the length of the string
			array(
				'method'    => array($this->mockValidator, 'checkString'),
				'params'    => array($testString, false, 3, 5),
				'errorCode' => ValidatorException::EC_LONG,
			),
			// Check when the string contains some invalid chars
			array(
				'method'    => array($this->mockValidator, 'checkString'),
				'params'    => array($testString, false, 3, 20, '#^[a-b0-9]*$#'),
				'errorCode' => ValidatorException::EC_INVALID_CHARS,
			),
		);

		foreach ($testCases as $testCase) {
			try {
				call_user_func_array($testCase['method'], $testCase['params']);
				if (isset($testCase['errorCode'])) {
					$this->fail('The method should thrown and Exception when an invalid parameter passed!');
				}
			}
			catch (ValidatorException $e) {
				if (isset($testCase['errorCode'])) {
					$this->assertEquals($testCase['errorCode'], $e->getCode());
				}
				else {
					$this->fail('This method should not have thrown Exception for this parameter');
				}
			}
			catch (\Exception $e) {
				if (!($e instanceof PHPUnit_Framework_AssertionFailedError)) {
					$this->fail('The method should throw a ValidatorException!');
				}
			}
		}
	}
}