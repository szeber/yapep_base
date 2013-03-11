<?php

namespace YapepBase\Exception;

use \YapepBase\Exception\TypeException;

/**
 * Test class for \YapepBase\Exception\TypeException.
 */
class TypeExceptionTest extends \YapepBase\BaseTest {
	/**
	 * Test throwing with a class
	 */
	public function testWithClass() {
		try {
			$class = new \stdClass();
			$expected = 'string';
			throw new \YapepBase\Exception\TypeException($class, $expected);
			$this->fail('Exception not thrown!');
		} catch (\YapepBase\Exception\TypeException $e) {
			$this->assertNotEquals(false, \strpos($e->getMessage(), \get_class($class)),
					'Class name ' . \get_class($class) . ' is not in message! Message was: ' . $e->getMessage());
			$this->assertNotEquals(false, \strpos($e->getMessage(), $expected),
					'Expected type ' . $expected . ' is not in message! Message was: ' . $e->getMessage());
		}
	}

	/**
	 * Test with a base type.
	 */
	public function testWithBaseType() {
		try {
			$type = 17;
			$expected = 'string';
			throw new \YapepBase\Exception\TypeException($type, $expected);
			$this->fail('Exception not thrown!');
		} catch (\YapepBase\Exception\TypeException $e) {
			$this->assertNotEquals(false, \strpos($e->getMessage(), \gettype($type)),
					'Class name ' . \gettype($type) . ' is not in message! Message was: ' . $e->getMessage());
			$this->assertNotEquals(false, \strpos($e->getMessage(), $expected),
					'Expected type ' . $expected . ' is not in message! Message was: ' . $e->getMessage());
		}
	}
}
