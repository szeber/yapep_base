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


use YapepBase\Mock\Autoloader\SimpleAutoloaderMock;

/**
 * Test class for SimpleAutoloader.
 *
 * @package    YapepBase
 * @subpackage Autoloader
 */
class SimpleAutoloaderTest extends \PHPUnit_Framework_TestCase {

	/**
	 * The SimpleAutoloader object.
	 *
	 * @var SimpleAutoloaderMock
	 */
	protected $simpleAutoloader;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp() {
		$this->simpleAutoloader = new SimpleAutoloaderMock();
	}

	/**
	 * Tests the getPaths() method.
	 *
	 * @return void
	 */
	public function testGetPaths() {
		$classPath1 = DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'path1';
		$classPath2 = DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'path2' . DIRECTORY_SEPARATOR;
		$classPath3 = DIRECTORY_SEPARATOR . 'test' . DIRECTORY_SEPARATOR . 'path3';

		$this->simpleAutoloader->addClassPath($classPath1);
		$this->simpleAutoloader->addClassPath($classPath2);
		$this->simpleAutoloader->addClassPath($classPath3, '\Test\Namespaced');

		$filePaths = $this->simpleAutoloader->getPaths('Test\TestClass');
		$expectedResult = array(
			$classPath1 . DIRECTORY_SEPARATOR . 'Test' . DIRECTORY_SEPARATOR . 'TestClass.php',
			$classPath2 . 'Test' . DIRECTORY_SEPARATOR . 'TestClass.php',
		);
		sort($filePaths);
		sort($expectedResult);

		$this->assertEquals($expectedResult, $filePaths);

		$filePaths = $this->simpleAutoloader->getPaths('Test\Namespaced\TestClass');
		$expectedResult = array(
			$classPath3 . DIRECTORY_SEPARATOR . 'Test' . DIRECTORY_SEPARATOR . 'Namespaced'
				. DIRECTORY_SEPARATOR . 'TestClass.php',
		);

		$this->assertSame($expectedResult, $filePaths,
			'In case of the namespaced loading only files from the given directory should be loaded');
	}

	/**
	 * Tests the loadClass() method.
	 *
	 * @return void
	 */
	public function testLoadClass() {
		$path = TEST_DIR . DIRECTORY_SEPARATOR
			. 'YapepBase' . DIRECTORY_SEPARATOR
			. 'TestData' . DIRECTORY_SEPARATOR
			. 'Autoloader' . DIRECTORY_SEPARATOR
			. 'Test' . DIRECTORY_SEPARATOR;

		// Check a class
		$classPath = $path . 'AutoloaderTestClass.php';
		$this->assertFalse(class_exists('\\YapepBase\\TestData\\Autoloader\\Test\\AutoloaderTestClass', false));
		$this->simpleAutoloader->loadClass($classPath, '\\YapepBase\\TestData\\Autoloader\\Test\\AutoloaderTestClass');
		$this->assertTrue(class_exists('\\YapepBase\\TestData\\Autoloader\\Test\\AutoloaderTestClass', false));

		// Check in Interface
		$interfacePath = $path . 'IAutoloaderTestInterface.php';
		$this->assertFalse(interface_exists('\\YapepBase\\TestData\\Autoloader\\Test\\IAutoloaderTestInterface', false));
		$this->simpleAutoloader->loadClass($interfacePath,
			'\\YapepBase\\TestData\\Autoloader\\Test\\IAutoloaderTestInterface');
		$this->assertTrue(interface_exists('\\YapepBase\\TestData\\Autoloader\\Test\\IAutoloaderTestInterface', false));
	}
}