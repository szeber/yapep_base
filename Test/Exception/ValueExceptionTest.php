<?php

namespace YapepBase\Exception;

use \YapepBase\Exception\ValueException;

/**
 * Test class for \YapepBase\Exception\ValueException.
 */
class ValueExceptionTest extends \PHPUnit_Framework_TestCase {
	/**
	 * Test an exception throw
	 */
	public function testThrow() {
		try {
			$value = -1;
			$expected = 'positive integer';
			throw new \YapepBase\Exception\ValueException($value, $expected);
			$this->fail('Exception not thrown!');
		} catch (\YapepBase\Exception\ValueException $e) {
			$this->assertNotEquals(false, \strpos($e->getMessage(), (string)$value),
					'Value ' . $expected . ' is not in message! Message was: ' . $e->getMessage());
			$this->assertNotEquals(false, \strpos($e->getMessage(), $expected),
					'Expected string ' . $expected . ' is not in message! Message was: ' . $e->getMessage());
		}
	}
}
