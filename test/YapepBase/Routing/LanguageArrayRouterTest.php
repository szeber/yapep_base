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


use YapepBase\Exception\RouterException;
use ReflectionMethod;
use YapepBase\Mock\Request\RequestMock;
use YapepBase\Router\LanguageArrayRouter;
use YapepBase\Request\IRequest;

/**
 * Test class for LanguageArrayRouter
 *
 * @package    YapepBase
 * @subpackage subpackage
 */
class LanguageArrayRouterTest extends \YapepBase\BaseTest {

	/**
	 * Contains the usable languages.
	 *
	 * @var array
	 */
	protected $languages = array('en', 'de', 'es', 'ja');

	/**
	 * The default language.
	 *
	 * @var string
	 */
	protected $defaultLanguage = 'en';

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
	 * @param string      $currentUri   The current URI.
	 * @param array       $rules        The routing rules.
	 * @param RequestMock $request      The request instance for the router. (Outgoing parameter)
	 *
	 * @return \YapepBase\Router\LanguageArrayRouter
	 */
	protected function getRouter($currentUri, array $rules, &$request = null) {
		$request = new RequestMock(preg_replace('/^\s*\[[^]]*\]/', '', $currentUri), IRequest::METHOD_HTTP_GET);
		return new LanguageArrayRouter($request, $rules, $this->defaultLanguage, $this->languages);
	}

	/**
	 * Tests the ability to determine the current language from the current URI.
	 *
	 * @return void
	 */
	public function testCurrentLanguage() {
		$router = $this->getRouter('/en/test', array());
		$this->assertEquals('en', $router->getLanguage());

		$router = $this->getRouter('/test', array());
		$this->assertEquals($this->defaultLanguage, $router->getLanguage());

		$router = $this->getRouter('/de/test', array());
		$this->assertEquals('de', $router->getLanguage());
	}

	/**
	 * Tests the getTarget() method.
	 *
	 * @return void
	 */
	public function testGetTarget() {
		$rules = array(
			'Index/Index'        => '/',
			'Index/Simple'       => '/test/simple',
			'Index/Language'     => '/test/en',
			'Index/WithoutSlash' => '/test'
		);

		foreach ($this->languages as $language) {
			foreach ($rules as $uri) {
				$router = $this->getRouter('/' . $language . $uri, $rules);
				$method = new ReflectionMethod($router, 'getTarget');
				$method->setAccessible(true);
				$result = $method->invoke($router);
				$this->assertEquals($uri, $result);
			}
		}
	}

	/**
	 * Tests whether simple routes work
	 *
	 * @return void
	 */
	public function testSimpleRoutes() {
		$rules = array(
			'Index/Index'        => '/',
			'Index/Simple'       => '/test/simple',
			'Index/Language'     => '/test/en',
			'Index/WithoutSlash' => '/test'
		);

		$controller = null;
		$action = null;

		foreach ($this->languages as $language) {
			foreach ($rules as $controllerAction => $uri) {
				$route = $this->getRouter('/' . $language . $uri, $rules)->getRoute($controller, $action);

				$this->assertEquals($controllerAction, $route, 'The route for an empty request does not match');
				$this->assertEquals($route, $controller . '/' . $action,
					'The outgoing parameters do not match the returned route');
			}
		}
	}

	/**
	 * Tests whether simple routes work
	 *
	 * @return void
	 */
	public function testWithNonExistentLanguage() {
		$rules = array(
			'Index/Index'        => '/',
			'Index/Simple'       => '/test/simple',
			'Index/Language'     => '/test/en',
			'Index/WithoutSlash' => '/test'
		);

		$controller = null;
		$action = null;

		foreach ($rules as $uri) {
			try {
				$this->getRouter('/pl' . $uri, $rules)->getRoute($controller, $action);
				$this->fail('Should drop en Exception in case of non-existent language');
			}
			catch (RouterException $e) {
			}
		}
	}
}
