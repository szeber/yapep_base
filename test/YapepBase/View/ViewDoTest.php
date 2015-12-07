<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   View
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\View;

use \PHPUnit_Framework_AssertionFailedError;
use ErrorException;

use YapepBase\View\ViewDo;
use YapepBase\Mime\MimeType;
use YapepBase\Application;

/**
 * Test case for testing the ViewDo class
 *
 * @package    YapepBase
 * @subpackage View
 */
class ViewDoTest extends \YapepBase\BaseTest {

	/**
	 * Thw View Data Object.
	 *
	 * @var ViewDo
	 */
	protected $viewDo;

	/**
	 * Runs before each test
	 *
	 * @return void
	 */
	protected function setUp() {
		parent::setUp();

		$this->viewDo = new ViewDo(MimeType::HTML);
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
	 * Tests the set and get method.
	 *
	 * @return void
	 */
	public function testSetAndGet() {
		$this->viewDo->set('testString', '1');
		$this->assertEquals('1', $this->viewDo->get('testString'));

		$this->viewDo->set('testArray', array('test' => 'testValue'));
		$this->assertEquals(array('test' => 'testValue'), $this->viewDo->get('testArray'));
		$this->assertEquals('testValue', $this->viewDo->get('testArray.test'));
	}

	/**
	 * Tests checkIsEmpty() method.
	 *
	 * @return void
	 */
	public function testCheckIsEmpty() {
		$this->assertTrue($this->viewDo->checkIsEmpty('testKey'));
		$this->assertTrue($this->viewDo->checkIsEmpty('testKey', true));

		$this->viewDo->set('testNull', null);
		$this->viewDo->set('testFalse', false);
		$this->viewDo->set('testTrue', true);
		$this->viewDo->set('test0', 0);
		$this->viewDo->set('test1', 1);
		$this->viewDo->set('testEmptyString', '');
		$this->viewDo->set('testEmptyArray', array());

		$this->assertTrue($this->viewDo->checkIsEmpty('testNull'));
		$this->assertFalse($this->viewDo->checkIsEmpty('testNull', true));

		$this->assertTrue($this->viewDo->checkIsEmpty('testFalse'));
		$this->assertFalse($this->viewDo->checkIsEmpty('testFalse', true));

		$this->assertFalse($this->viewDo->checkIsEmpty('testTrue'));

		$this->assertTrue($this->viewDo->checkIsEmpty('test0'));

		$this->assertFalse($this->viewDo->checkIsEmpty('test1'));

		$this->assertTrue($this->viewDo->checkIsEmpty('testEmptyString'));
		$this->assertFalse($this->viewDo->checkIsEmpty('testEmptyString', true));

		$this->assertTrue($this->viewDo->checkIsEmpty('testEmptyArray'));
		$this->assertFalse($this->viewDo->checkIsEmpty('testEmptyArray', true));
	}

	/**
	 * Tests the checkIsArray() method.
	 *
	 * @return void
	 */
	public function testCheckIsArray() {
		$this->assertFalse($this->viewDo->checkIsArray('testKey'));

		$this->viewDo->set('testNull', null);
		$this->viewDo->set('testEmptyArray', array());
		$this->viewDo->set('testArray', array('test' => array('testValue')));

		$this->assertFalse($this->viewDo->checkIsArray('testNull'));
		$this->assertTrue($this->viewDo->checkIsArray('testEmptyArray'));
		$this->assertTrue($this->viewDo->checkIsArray('testArray'));
		$this->assertTrue($this->viewDo->checkIsArray('testArray.test'));
		$this->assertFalse($this->viewDo->checkIsArray('testArray.test.test'));
	}

	/**
	 * Tests the clear() method.
	 *
	 * @return void
	 */
	public function testClear() {

		$this->viewDo->set('test1', 1);
		$this->viewDo->set('test2', 2);

		$this->assertEquals(1, $this->viewDo->get('test1'));
		$this->assertEquals(2, $this->viewDo->get('test2'));

		$this->viewDo->clear();

		try {
			$this->assertNull($this->viewDo->get('test1'));
			$this->fail('This method should trigger an error when a nonexistent key accessed.');
		}
		catch (\Exception $e) {
			$this->assertTrue($e instanceof ErrorException, 'Should have thrown an ErrorException');
		}

		try {
			$this->assertNull($this->viewDo->get('test2'));
			$this->fail('This method should trigger an error when a nonexistent key accessed.');
		}
		catch (\Exception $e) {
			$this->assertTrue($e instanceof ErrorException, 'Should have thrown an ErrorException');
		}
	}


	/**
	 * Tests the escape of the set data.
	 *
	 * @return void
	 */
	public function testEscape() {
		$testString = '<a href="http://test.com/index.php?test=1&test2=2">test</a>';

		$this->viewDo->set('testString', $testString);
		$this->assertEquals(htmlspecialchars($testString), $this->viewDo->get('testString'));

		$testArray = array(array($testString));
		$this->viewDo->set('testArray', $testArray);
		$this->assertEquals(array(array(htmlspecialchars($testString))), $this->viewDo->get('testArray'));
	}


	public function testToArray_whenRawRequired_shouldReturnRawData() {
		$testString = '<a href="http://test.com/index.php?test=1&test2=2">test</a>';

		$this->viewDo->set('testString', $testString);

		$expected = array(
			'testString' => $testString
		);
		$this->assertEquals($expected, $this->viewDo->toArray(true));
	}


	public function testToArray_whenEscapedRequired_shouldReturnEscapedData() {
		$testString = '<a href="http://test.com/index.php?test=1&test2=2">test</a>';

		$this->viewDo->set('testString', $testString);

		$expected = array(
			'testString' => htmlspecialchars($testString)
		);
		$this->assertEquals($expected, $this->viewDo->toArray());
	}
}
