<?php

namespace YapepBase\Autoloader;

class AutoloaderRegistryTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var AutoloaderRegistryMock
	 */
	protected $object;

	public function setUp() {
		$this->object = new \YapepBase\Test\Mock\Autoloader\AutoloaderRegistryMock();
	}

	public function testRegister() {
		$this->assertFalse($this->object->spl);
		$al = new \YapepBase\Test\Mock\Autoloader\AutoloaderMock();
		$this->object->register($al);
		$this->assertTrue($this->object->spl);
		$this->assertTrue($this->object->load('NonExistent'));
		$this->assertEquals(array('NonExistent' => 'NonExistent'), $al->loaded);
		$this->object->unregister($al);
		$this->assertFalse($this->object->spl);
		$this->assertFalse($this->object->load('NonExistent2'));
		$this->assertEquals(array('NonExistent' => 'NonExistent'), $al->loaded);

		$al = new \YapepBase\Test\Mock\Autoloader\AutoloaderMock();
		$this->object->setAutoregister(false);
		$this->object->register($al);
		$this->assertFalse($this->object->spl);
		$this->assertTrue($this->object->load('NonExistent'));
		$this->assertEquals(array('NonExistent' => 'NonExistent'), $al->loaded);
		$this->object->unregisterByClass('YapepBase\Test\Mock\Autoloader\AutoloaderMock');
		$this->assertFalse($this->object->load('NonExistent2'));
		$this->assertEquals(array('NonExistent' => 'NonExistent'), $al->loaded);
	}
}
