<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Test\Routing
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Test\Routing;
use YapepBase\Request\IRequest;
use YapepBase\Test\Mock\Request\RequestMock;
use YapepBase\Router\AutoRouter;

/**
 * AutoRouterTest class
 *
 * @package    YapepBase
 * @subpackage subpackage
 */
class AutoRouterTest extends \PHPUnit_Framework_TestCase {

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
	 * Returns a router object, with the request set up to the provided target and method.
	 *
	 * @param string      $target
	 * @param RequestMock $request   The request instance for the router. (Outgoing parameter)
	 *
	 * @return \YapepBase\Router\AutoRouter
	 */
	protected function getRouter($target = '', &$request = null) {
		$request = new RequestMock($target, IRequest::METHOD_HTTP_GET);
		return new AutoRouter($request);
	}

	/**
	 * Tests whether simple routes work
	 */
	public function testRouting() {
		$controller = null;
		$action = null;

		$route = $this->getRouter('')->getRoute($controller, $action);
		$this->assertEquals('Index/Index', $route, 'The route for an empty request does not match');
		$this->assertEquals($route, $controller . '/' . $action,
			'The outgoing parameters do not match the returned route');

		$route = $this->getRouter('/test/')->getRoute();
		$this->assertEquals('Test/Index', $route, 'The route for an index action request does not match');

		$route = $this->getRouter('/test/test_one-two')->getRoute();
		$this->assertEquals('Test/TestOneTwo', $route, 'The route for a normal request does not match');

		$request = null;

		$route = $this->getRouter('/test/test/param1/param2', $request)->getRoute();
		$this->assertEquals('Test/Test', $route, 'The route for a parameterized request does not match');
		$this->assertEquals('param1', $request->getParam(0), 'The first param does not match');
		$this->assertEquals('param2', $request->getParam(1), 'The second param does not match');
		$this->assertEquals(2, count($request->getAllParams()), 'The param count does not match');
	}

	/**
	 * Tests whether route creation from controller and action works
	 */
	public function testRouteCreation() {
		$router = $this->getRouter('');

		$this->assertEquals('/', $router->getTargetForControllerAction('Index', 'Index'));
		$this->assertEquals('/index/index/test', $router->getTargetForControllerAction('Index', 'Index',
			array('test')));
		$this->assertEquals('/test', $router->getTargetForControllerAction('Test', 'Index'));
		$this->assertEquals('/test/testAction', $router->getTargetForControllerAction('Test', 'TestAction'));

	}

}
