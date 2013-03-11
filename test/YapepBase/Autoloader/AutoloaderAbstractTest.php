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


use YapepBase\Mock\Autoloader\AutoloaderMock;

/**
 * Test class for AutoloaderAbstract.
 *
 * @package    YapepBase
 * @subpackage Autoloader
 */
class AutoloaderAbstractTest extends \YapepBase\BaseTest {

	/**
	 * The autoloader object to test.
	 *
	 * @var \YapepBase\Mock\Autoloader\AutoloaderMock
	 */
	protected $autoloader;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp() {
		parent::setUp();
		$this->autoloader = new AutoloaderMock();
	}

	/**
	 * Tests the addClassPath() method.
	 *
	 * @return void
	 */
	public function testAddClassPath() {
		$classPath = '/test/test1';
		$classPath2 = '/test/test2';
		$this->autoloader->addClassPath($classPath);
		$this->autoloader->addClassPath($classPath2 . '////');

		$this->assertEquals($classPath, $this->autoloader->classPaths[0]);
		$this->assertEquals($classPath2, $this->autoloader->classPaths[1]);

		$classPathForNamespace = '/test/namespace';
		$namespace = 'Test\\Test';

		$this->autoloader->addClassPath($classPathForNamespace, $namespace);

		$this->assertEquals($classPathForNamespace, $this->autoloader->classPathsWithNamespace[$namespace]);
	}
}
