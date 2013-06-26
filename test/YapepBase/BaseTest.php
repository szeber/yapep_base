<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase;


/**
 * Base class for unit tests
 *
 * @package    YapepBase
 */
abstract class BaseTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp() {
		parent::setUp();

		// None of the descendant test cases should be run as root.
		if (function_exists('posix_getuid') && 0 == posix_getuid()) {
			$this->markTestSkipped('This test may not be run as root');
		}
	}


}
