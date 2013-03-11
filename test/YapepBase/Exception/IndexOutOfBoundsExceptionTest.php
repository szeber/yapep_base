<?php

namespace PHP\Lang;

use \YapepBase\Exception\IndexOutOfBoundsException;

/**
 *  This class tests the functionality of \YapepBase\Exception\IndexOutOfBoundsException
 */
class IndexOutOfBoundsExceptionTest extends \YapepBase\BaseTest {
	/**
	 * Tests throwing the exception with an offset.
	 */
	public function testThrowWithOffset() {
		try {
			$offset = 12;
			throw new \YapepBase\Exception\IndexOutOfBoundsException($offset);
			$this->fail('Exception not thrown!');
		} catch (\YapepBase\Exception\IndexOutOfBoundsException $e) {
			$this->assertNotEquals(false, \strpos($e->getMessage(), (string)$offset),
					'Offset ' . $offset . ' is not in message! Message was: ' . $e->getMessage());
		}
	}

	/**
	 * Tests throwing the exception without an offset
	 */
	public function testThrowWithoutOffset() {
		try {
			throw new \YapepBase\Exception\IndexOutOfBoundsException();
			$this->fail('Exception not thrown!');
		} catch (\YapepBase\Exception\IndexOutOfBoundsException $e) {
			$this->assertNotEquals('', $e->getMessage(), 'Message is empty!');
		}
	}
}
