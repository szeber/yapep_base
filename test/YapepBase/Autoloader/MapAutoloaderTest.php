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


use YapepBase\Autoloader\MapAutoloader;

/**
 * Test class for MapAutoloader.
 *
 * @package    YapepBase
 * @subpackage Autoloader
 */
class MapAutoloaderTest extends \YapepBase\BaseTest {
	/**
	 * Tests the loadClass() method.
	 *
	 * @return void
	 */
	public function testLoad() {
		$path = TEST_DIR . DIRECTORY_SEPARATOR
			. 'YapepBase' . DIRECTORY_SEPARATOR
			. 'TestData' . DIRECTORY_SEPARATOR
			. 'Autoloader' . DIRECTORY_SEPARATOR
			. 'Test' . DIRECTORY_SEPARATOR;

		$classPath = $path . 'MapAutoloaderTestClass.php';
		$interfacePath = $path . 'IMapAutoloaderTestInterface.php';
		$classMap = array(
			'MapAutoloaderTestClass'      => $classPath,
			'IMapAutoloaderTestInterface' => $interfacePath,
		);

		$autoloader = new MapAutoloader($classMap);

		// Check a class
		$this->assertFalse(class_exists('MapAutoloaderTestClass', false));
		$autoloader->load('MapAutoloaderTestClass');
		$this->assertTrue(class_exists('MapAutoloaderTestClass', false));

		// Check in Interface
		$this->assertFalse(interface_exists('IMapAutoloaderTestInterface', false));
		$autoloader->load('IMapAutoloaderTestInterface');
		$this->assertTrue(interface_exists('IMapAutoloaderTestInterface', false));
	}
}