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
use YapepBase\Exception\RouterException;
use YapepBase\Test\Mock\Request\RequestMock;
use YapepBase\Router\ArrayRouter;
use YapepBase\Request\IRequest;

/**
 * ArrayRouterTest class
 *
 * @package    YapepBase
 * @subpackage subpackage
 */
class ArrayRouterTest extends \PHPUnit_Framework_TestCase {

	/**
	 * Stores the available routes
	 *
	 * @var array
	 */
	protected $routes = array(
		'Simple/Empty'           => '/',
		'Simple/Normal'          => '/normal',
		'Simple/WithoutSlash'    => 'withoutslash',
		'Simple/Get'             => '[GET]/method',
		'Simple/Post'            => '[POST]/method',
		'Param/SimpleAlpha'      => '/param/simplealpha/{param:alpha}',
		'Param/SimpleAlnum'      => '/param/simplealnum/{param:alnum}',
		'Param/SimpleNum'        => '/param/simplenum/{param:num}',
		'Param/SimpleEnum'       => '/param/simpleenum/{param:enum(test|test2|test3)}',
		'Param/SimpleRegex'      => '/param/simpleregex/{param:regex([Tt]est)}',
		'Param/ComplexParam'     => '/param/complexparam/{param1:alnum}/{param2:enum(test1|test2)}/{param3:alnum}',
		'Invalid/Syntax'         => '/invalid/syntax/{param:alpha',
		'Invalid/ParamType'      => '/invalid/paramtype/{param:invalid}',
		'Invalid/DuplicateParam' => '/invalid/duplicateparam/{param:alnum}/{param:alnum}',
		'Invalid/Regex'          => '/invalid/regex/{param:regex}',
		'Invalid/Enum'           => '/invalid/enum/{param:enum}',
	);

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
	 * @param string      $method
	 * @param RequestMock $request   The request instance for the router. (Outgoing parameter)
	 *
	 * @return \YapepBase\Router\ArrayRouter
	 */
	protected function getRouter($target = '', $method = IRequest::METHOD_HTTP_GET, &$request = null) {
		$request = new RequestMock(preg_replace('/^\s*\[[^]]*\]/', '', $target), $method);
		return new ArrayRouter($request, $this->routes);
	}

	/**
	 * Tests whether simple routes work
	 */
	public function testSimpleRoutes() {
		$controller = null;
		$action = null;

		$route = $this->getRouter($this->routes['Simple/Empty'])->getRoute($controller, $action);
		$this->assertEquals('Simple/Empty', $route, 'The route for an empty request does not match');
		$this->assertEquals($route, $controller . '/' . $action,
			'The outgoing parameters do not match the returned route');

		$route = $this->getRouter($this->routes['Simple/Normal'])->getRoute($controller, $action);
		$this->assertEquals('Simple/Normal', $route, 'The route for a normal request does not match');

		$route = $this->getRouter($this->routes['Simple/WithoutSlash'])->getRoute($controller, $action);
		$this->assertEquals('Simple/WithoutSlash', $route, 'The route for a request without a slash does not match');

		$route = $this->getRouter($this->routes['Simple/Get'])->getRoute($controller, $action);
		$this->assertEquals('Simple/Get', $route, 'The route for a GET restricted request does not match');

		$route = $this->getRouter($this->routes['Simple/Post'], IRequest::METHOD_HTTP_POST)
			->getRoute($controller, $action);
		$this->assertEquals('Simple/Post', $route, 'The route for a POST restricted request does not match');
	}

	/**
	 * Tests if the parameterized routes work
	 */
	public function testParamRoutes() {
		$request = null;

		$route = $this->getRouter('/param/simplealpha/test', IRequest::METHOD_HTTP_GET, $request)
			->getRoute();
		$this->assertEquals('Param/SimpleAlpha', $route, 'The route for the alpha param route request does not match');
		$this->assertEquals('test', $request->getParam('param'), 'The alpha param route param does not match');

		$route = $this->getRouter('/param/simplenum/2', IRequest::METHOD_HTTP_GET, $request)
			->getRoute();
		$this->assertEquals('Param/SimpleNum', $route, 'The route for the numeric param route request does not match');
		$this->assertEquals(2, $request->getParam('param'), 'The numeric param route param does not match');

		$route = $this->getRouter('/param/simplealnum/test2', IRequest::METHOD_HTTP_GET, $request)
			->getRoute();
		$this->assertEquals('Param/SimpleAlnum', $route, 'The route for the alnum param route request does not match');
		$this->assertEquals('test2', $request->getParam('param'), 'The alnum param route param does not match');

		$route = $this->getRouter('/param/simpleenum/test3', IRequest::METHOD_HTTP_GET, $request)
			->getRoute();
		$this->assertEquals('Param/SimpleEnum', $route, 'The route for the enum param route request does not match');
		$this->assertEquals('test3', $request->getParam('param'), 'The enum param route param does not match');

		$route = $this->getRouter('/param/simpleregex/Test', IRequest::METHOD_HTTP_GET, $request)
			->getRoute();
		$this->assertEquals('Param/SimpleRegex', $route, 'The route for the regex param route request does not match');
		$this->assertEquals('Test', $request->getParam('param'), 'The regex param route param does not match');

		$route = $this->getRouter('/param/complexparam/test1/test2/test3', IRequest::METHOD_HTTP_GET, $request)
			->getRoute();
		$this->assertEquals('Param/ComplexParam', $route, 'The route for an alpha param route request does not match');
		$this->assertEquals('test1', $request->getParam('param1'),
			'The complex param route first param does not match');
		$this->assertEquals('test2', $request->getParam('param2'),
			'The complex param route second param does not match');
		$this->assertEquals('test3', $request->getParam('param3'),
			'The complex param route third param does not match');
		$this->assertEquals(3, count($request->getAllParams()));
	}

	/**
	 * Tests if the correct error is thrown for a non existing route
	 */
	public function testNotFoundRoute() {
		$this->setExpectedException('\YapepBase\Exception\RouterException', '', RouterException::ERR_NO_ROUTE_FOUND);

		$this->getRouter('/param/simplealpha/test1')->getRoute();
	}

	/**
	 * Tests if the correct error is thrown for a route with a param syntax error
	 */
	public function testSyntaxErrorRoute() {
		$this->setExpectedException('\YapepBase\Exception\RouterException', '', RouterException::ERR_SYNTAX_PARAM);

		$this->getRouter('/invalid/syntax/test')->getRoute();
	}

	/**
	 * Tests if the correct error is thrown for a route with an invalid type
	 */
	public function testTypeErrorRoute() {
		$this->setExpectedException('\YapepBase\Exception\RouterException', '', RouterException::ERR_SYNTAX_PARAM);

		$this->getRouter('/invalid/paramtype/test')->getRoute();
	}

	/**
	 * Tests if the correct error is thrown for a route with an invalid type
	 */
	public function testDuplicateParamRoute() {
		$this->setExpectedException('\YapepBase\Exception\RouterException', '', RouterException::ERR_SYNTAX_PARAM);

		$this->getRouter('/invalid/duplicateparam/test/test')->getRoute();
	}

	/**
	 * Tests if the correct error is thrown for a route with an enum param without options
	 */
	public function testEnumErrorRoute() {
		$this->setExpectedException('\YapepBase\Exception\RouterException', '', RouterException::ERR_SYNTAX_PARAM);

		$this->getRouter('/invalid/enum/test')->getRoute();
	}

	/**
	 * Tests if the correct error is thrown for a route with a regex param without a pattern
	 */
	public function testRegexErrorRoute() {
		$this->setExpectedException('\YapepBase\Exception\RouterException', '', RouterException::ERR_SYNTAX_PARAM);

		$this->getRouter('/invalid/regex/test')->getRoute();
	}

	/**
	 * Tests whether route creation works
	 */
	public function testRouteCreation() {
		$router = $this->getRouter('');

		$this->assertEquals('/' . $this->routes['Simple/WithoutSlash'],
			$router->getTargetForControllerAction('Simple', 'WithoutSlash'),
			'The created route does not match for a simple target');

		$this->assertEquals('/param/simplealpha/test',
			$router->getTargetForControllerAction('Param', 'SimpleAlpha', array('param' => 'test')),
			'The created route does not match for a simple target');
	}

	/**
	 * Tests if a correct error is thrown for a non existing route creation
	 */
	public function testNonExistingRouteCreation() {
		$this->setExpectedException('\YapepBase\Exception\RouterException', '', RouterException::ERR_NO_ROUTE_FOUND);

		$router = $this->getRouter()->getTargetForControllerAction('Non', 'Existing');
	}

	/**
	 * Tests if a correct error is thrown for route creation with a missing required param
	 */
	public function testRouteCreationWithMissingParam() {
		$this->setExpectedException('\YapepBase\Exception\RouterException', '', RouterException::ERR_MISSING_PARAM);

		$router = $this->getRouter()->getTargetForControllerAction('Param', 'SimpleAlpha');
	}
}
