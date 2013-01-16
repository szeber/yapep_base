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


use YapepBase\Router\ArrayReverseRouter;
use YapepBase\Exception\RouterException;

/**
 * Test class for ArrayReverseRouter
 *
 * @package    YapepBase
 * @subpackage subpackage
 */
class ArrayReverseRouterTest extends \PHPUnit_Framework_TestCase {

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
			'Simple/Empty'           => '/',
			'Simple/Normal'          => '/normal',
			'Simple/Get'             => '[GET]/get',
			'Param/SimpleAlpha'      => '/param/simplealpha/{param:alpha}',
			'Param/SimpleAlnum'      => '/param/simplealnum/{param:alnum}',
			'Param/SimpleNum'        => '/param/simplenum/{param:num}',
			'Param/SimpleEnum'       => '/param/simpleenum/{param:enum(test|test2|test3)}',
			'Param/SimpleRegex'      => '/param/simpleregex/{param:regex([Tt]est)}',
			'Param/ComplexParam'     => '/param/complexparam/{param1:alnum}/{param2:enum(test1|test2)}/{param3:alnum}',
		);
		$paramsForRules = array(
			'Param/SimpleAlpha'      => array('param' => 'test'),
			'Param/SimpleAlnum'      => array('param' => 'test1'),
			'Param/SimpleNum'        => array('param' => 12),
			'Param/SimpleEnum'       => array('param' => 'test1'),
			'Param/SimpleRegex'      => array('param' => 'Test'),
			'Param/ComplexParam'     => array('param1' => 'test1', 'param2' => 'test1', 'param3' => 'test89'),
		);
		$expectedResults = array(
			'Simple/Empty'           => '/',
			'Simple/Normal'          => '/normal',
			'Simple/Get'             => '/get',
			'Param/SimpleAlpha'      => '/param/simplealpha/test',
			'Param/SimpleAlnum'      => '/param/simplealnum/test1',
			'Param/SimpleNum'        => '/param/simplenum/12',
			'Param/SimpleEnum'       => '/param/simpleenum/test1',
			'Param/SimpleRegex'      => '/param/simpleregex/Test',
			'Param/ComplexParam'     => '/param/complexparam/test1/test1/test89',
		);

		$reverseRouter = new ArrayReverseRouter($rules);
		foreach ($rules as $controllerAction => $uri) {
			list($controller, $action) = explode('/', $controllerAction);
			$params = array();
			if (array_key_exists($controllerAction, $paramsForRules)) {
				$params = $paramsForRules[$controllerAction];
			}
			$generatedUri = $reverseRouter->getTargetForControllerAction($controller, $action, $params);

			$this->assertEquals($expectedResults[$controllerAction], $generatedUri);
		}
	}

	/**
	 * Tests if the reverse routing works correctly with multiple routes for the same controller and action.
	 *
	 * @return void
	 */
	public function testMultiRoute() {
		// Make sure the routes are arranged in a way, where the ordering does not matter
		$rules = array(
			'Multiroute/Multiparam'  => array(
				'/multiroute/multiparam/{param1:alnum}',
				'/multiroute/multiparam/{param1:alnum}/{param2:alnum}/{param3:alnum}',
				'/multiroute/multiparam',
				'/multiroute/multiparam/{param1:alnum}/{param2:alnum}',
			),
		);

		$reverseRouter = new ArrayReverseRouter($rules);

		// Check if the not parameterised routing works
		$this->assertEquals('/multiroute/multiparam',
			$reverseRouter->getTargetForControllerAction('Multiroute', 'Multiparam'),
			'Invalid result for non-parameterised route');

		// Check if the route with 1 param works
		$this->assertEquals('/multiroute/multiparam/test1',
			$reverseRouter->getTargetForControllerAction('Multiroute', 'Multiparam', array('param1' => 'test1')),
			'Invalid result for 1 param route');

		// Check if the route with 2 param works
		$this->assertEquals('/multiroute/multiparam/test1/test2',
			$reverseRouter->getTargetForControllerAction('Multiroute', 'Multiparam', array(
				'param1' => 'test1',
				'param2' => 'test2'
			)),
			'Invalid result for 2 param route');

		// Check if the route with 3 param works
		$this->assertEquals('/multiroute/multiparam/test1/test2/test3',
			$reverseRouter->getTargetForControllerAction('Multiroute', 'Multiparam', array(
				'param1' => 'test1',
				'param2' => 'test2',
				'param3' => 'test3',
			)),
			'Invalid result for 3 param route');

		// Check if a param with an invalid name correctly throws an error
		try {
			$reverseRouter->getTargetForControllerAction('Multiroute', 'Multiparam', array('param' => 'test1'));
			$this->fail('No exception is thrown for a route with an invalid param');
		} catch (RouterException $e) {
			$this->assertContains('No exact route match for controller and action', $e->getMessage(),
				'Invalid exception message');
		}
	}
}
