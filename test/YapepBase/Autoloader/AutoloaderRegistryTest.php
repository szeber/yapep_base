<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Autoloader
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Autoloader;


use YapepBase\Mock\Autoloader\AutoloaderRegistryMock;
use YapepBase\Mock\Autoloader\AutoloaderMock;

/**
 * Test class for AutoloaderRegistry.
 *
 * @package    YapepBase
 * @subpackage Autoloader
 */
class AutoloaderRegistryTest extends \PHPUnit_Framework_TestCase {

	/**
	 * The autoloader registry object.
	 *
	 * @var AutoloaderRegistryMock
	 */
	protected $autoloaderRegistry;

	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	public function setUp() {
		$this->autoloaderRegistry = new AutoloaderRegistryMock();
	}

	/**
	 * Tests the addAutoloader() method.
	 *
	 * @return void
	 */
	public function testAddAutoloader() {
		$autoloader1 = new AutoloaderMock(1);
		$autoloader2 = new AutoloaderMock(2);

		$this->autoloaderRegistry->addAutoloader($autoloader1);
		$this->autoloaderRegistry->addAutoloader($autoloader2);

		$this->assertEquals($autoloader1, $this->autoloaderRegistry->registeredAutoloaders[0]);
		$this->assertEquals($autoloader2, $this->autoloaderRegistry->registeredAutoloaders[1]);
	}

	/**
	 * Tests the prependAutoloader() method.
	 *
	 * @return void
	 */
	public function testPrependAutoloader() {
		$autoloader1 = new AutoloaderMock(1);
		$autoloader2 = new AutoloaderMock(2);
		$autoloader3 = new AutoloaderMock(3);

		$this->autoloaderRegistry->addAutoloader($autoloader1);
		$this->autoloaderRegistry->addAutoloader($autoloader2);
		$this->autoloaderRegistry->prependAutoloader($autoloader3);

		$this->assertEquals($autoloader3, $this->autoloaderRegistry->registeredAutoloaders[0]);
		$this->assertEquals($autoloader1, $this->autoloaderRegistry->registeredAutoloaders[1]);
		$this->assertEquals($autoloader2, $this->autoloaderRegistry->registeredAutoloaders[2]);
	}

	/**
	 * Tests the clear() method.
	 *
	 * @return void
	 */
	public function testClear() {
		$autoloader1 = new AutoloaderMock(1);
		$autoloader2 = new AutoloaderMock(2);

		$this->autoloaderRegistry->addAutoloader($autoloader1);
		$this->autoloaderRegistry->addAutoloader($autoloader2);
		$this->autoloaderRegistry->clear();

		$this->assertEmpty($this->autoloaderRegistry->registeredAutoloaders);
	}

	/**
	 * Tests the load() method.
	 *
	 * @return void
	 */
	public function testLoad() {
		$autoloaderFail = new AutoloaderMock(1, false);
		$autoloaderSuccess = new AutoloaderMock(2, true);

		$this->autoloaderRegistry->addAutoloader($autoloaderFail);
		$this->autoloaderRegistry->addAutoloader($autoloaderSuccess);

		$className1 = 'class1';
		$className2 = 'class2';
		$result1 = $this->autoloaderRegistry->load($className1);
		$result2 = $this->autoloaderRegistry->load($className2);

		$this->assertTrue($result1);
		$this->assertTrue($result2);
		$this->assertEmpty($autoloaderFail->loadedClasses);
		$this->assertEquals(array($className1, $className2), $autoloaderSuccess->loadedClasses);
	}
}
