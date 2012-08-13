<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   BusinessObject
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\BusinessObject;

use YapepBase\Application;
use YapepBase\Config;
use YapepBase\DependencyInjection\SystemContainer;
use YapepBase\Mock\Storage\StorageMock;
use YapepBase\Mock\BusinessObject\MockBo;
use YapepBase\Exception\ParameterException;

/**
 * Test case for testing the BoAbstract class
 *
 * @package    YapepBase
 * @subpackage BusinessObject
 */
class BoAbstractTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \YapepBase\DependencyInjection\SystemContainer
	 */
	protected $originalDiContainer;

	/**
	 *
	 *
	 * @var \YapepBase\Mock\Storage\StorageMock
	 */
	protected $storage;

	/**
	 * Runs before each test
	 *
	 * @return void
	 */
	protected function setUp() {
		parent::setUp();
		$this->originalDiContainer = Application::getInstance()->getDiContainer();
		$diContainer = new SystemContainer();
		$this->storage = new StorageMock(true, false, array());

		$diContainer->setMiddlewareStorage($this->storage);
		Application::getInstance()->setDiContainer($diContainer);

		Config::getInstance()->set(array(
			'system.project.name' => 'test',
		));
	}

	/**
	 * Runs after each test
	 *
	 * @return void
	 */
	protected function tearDown() {
		parent::tearDown();
		Application::getInstance()->setDiContainer($this->originalDiContainer);
		Config::getInstance()->clear();
	}

	/**
	 * Tests the caching
	 *
	 * @return void
	 */
	public function testCaching() {
		$bo = new MockBo();
		$this->assertTrue(empty($this->storage->data), 'The data in the storage is not empty prior to testing');
		$this->assertSame($this->storage, $bo->getStorage(),
			'The retrieved middleware storage is not the one set in the DI container');

		$testData = array('testKey' => 'testVal');
		$bo->setToStorage('test', $testData);

		$this->assertEquals(2, count($this->storage->data),
			'After setting data to the storage, there should be 2 keys');

		$this->assertSame($testData, $bo->getFromStorage('test'), 'The stored and retrieved data does not match');

		$bo->deleteFromStorage('test');

		$this->assertEquals(1, count($this->storage->data), 'There should be 1 key after deleting');

		$this->assertEquals(0, count(reset($this->storage->data)), 'The key list should be empty after deleting');

		$this->assertFalse($bo->getFromStorage('test'), 'The get method should return FALSE after deleting the key');
	}

	/**
	 * Tests the caching of empty values.
	 *
	 * @return void
	 */
	public function testCachingEmptyValues() {
		$bo = new MockBo();

		$bo->setToStorage('test', null);
		$this->assertFalse($bo->getFromStorage('test'), 'Null should not be stored');

		$bo->setToStorage('test', 0);
		$this->assertFalse($bo->getFromStorage('test'), '0 should not be stored');

		$bo->setToStorage('test', '0');
		$this->assertFalse($bo->getFromStorage('test'), '\'0\' should not be stored');

		$bo->setToStorage('test', false);
		$this->assertFalse($bo->getFromStorage('test'), 'false should not be stored');

		// Testing the same with forcing the Bo to store empty values
		$bo->setToStorage('test', null, 0, true);
		$this->assertEquals(null, $bo->getFromStorage('test'), 'Null should not be stored');

		$bo->setToStorage('test', 0, 0, true);
		$this->assertEquals(0, $bo->getFromStorage('test'), '0 should not be stored');

		$bo->setToStorage('test', '0', 0, true);
		$this->assertEquals('0', $bo->getFromStorage('test'), '\'0\' should not be stored');

		// Be cautious when modifying this, because it depends on the last stored value
		$bo->setToStorage('test', false, 0, true);
		$this->assertEquals('0', $bo->getFromStorage('test'), 'false should not be stored');
	}

	/**
	 * Tests the deletion options
	 *
	 * @return void
	 */
	public function testDeletion() {
		$bo = new MockBo();

		$testData = array(
			'testKey' => 'testVal',
		);

		$bo->setToStorage('test1.data1', $testData);
		$bo->setToStorage('test1.data2', $testData);

		$bo->setToStorage('test2.data', $testData);
		$bo->setToStorage('test3.data', $testData);

		$this->assertEquals(5, count($this->storage->data),
			'There should be 5 keys in the storage after setting the test data');

		$bo->deleteFromStorage('test1.*');

		$this->assertEquals(3, count($this->storage->data),
			'There should be 3 values in the storage after deleting test1.*');

		$this->assertFalse($bo->getFromStorage('test1.data1'), 'test1.data1 should be deleted');
		$this->assertFalse($bo->getFromStorage('test1.data2'), 'test1.data2 should be deleted');

		$bo->deleteFromStorage();

		$this->assertEquals(1, count($this->storage->data), 'There should be 1 value in the storage after deleting all data');
	}

	/**
	 * Tests the error handling
	 *
	 *@return void
	 */
	public function testErrorHandling() {
		$bo = new MockBo();

		try {
			$bo->getFromStorage('');
			$this->fail('Getting from the storage with an empty key should throw a ParameterException');
		} catch (ParameterException $e) {
		}
		try {
			$bo->setToStorage('', 'test');
			$this->fail('Setting to the storage with an empty key should throw a ParameterException');
		} catch (ParameterException $e) {
		}
	}

}