<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Test\Routing
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Routing;


use YapepBase\Router\LanguageArrayReverseRouter;

/**
 * Test class for LanguageArrayReverseRouter
 *
 * @package    YapepBase
 * @subpackage subpackage
 */
class LanguageArrayReverseRouterTest extends \YapepBase\BaseTest {

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp();
	}

	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		parent::tearDown();
	}

	/**
	 * Tests the getTarget() method.
	 *
	 * @return void
	 */
	public function testGetTargetForControllerAction() {
		$rules = array(
			'Simple/Empty'  => '/',
			'Simple/Normal' => '/normal',
			'Simple/Deep'   => '/first/second/third'
		);

		// Test with a language different than the default one
		$reverseRouter = new LanguageArrayReverseRouter($rules, 'de', 'en');
		foreach ($rules as $controllerAction => $uri) {
			list($controller, $action) = explode('/', $controllerAction);
			$generatedUri = $reverseRouter->getTargetForControllerAction($controller, $action);

			$this->assertEquals('/de' . $uri, $generatedUri);
		}

		// Test with the same as the default
		$reverseRouter = new LanguageArrayReverseRouter($rules, 'de', 'de');
		foreach ($rules as $controllerAction => $uri) {
			list($controller, $action) = explode('/', $controllerAction);
			$generatedUri = $reverseRouter->getTargetForControllerAction($controller, $action);

			$this->assertEquals($uri, $generatedUri);
		}
	}
}
