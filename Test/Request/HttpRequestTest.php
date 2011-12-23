<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Test\Request
 * @author       bpinter
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Test\Request;
use YapepBase\Request\HttpRequest;

/**
 * HttpRequestTest class
 *
 * @package    YapepBase
 * @subpackage Test\Request
 */
class HttpRequestTest extends \PHPUnit_Framework_TestCase {

	/**
	 * The request
	 *
	 * @var \YapepBase\Request\HttpRequest
	 */
	protected $request;

    /**
     * Prepares the environment before running a test.
     */
	protected function setUp() {

		parent::setUp();

		$this->request = $this->getRequest();
	}

	/**
	 * Returns a request instance
	 *
	 * @param array $additionalServerFields   Any additional fields for the server array
	 *
	 * @return \YapepBase\Request\HttpRequest
	 */
	protected function getRequest(array $additionalServerFields = array()) {
		$server = array_merge(array(
			'REQUEST_URI'    => '/target',
            'REQUEST_METHOD' => 'post',
		), $additionalServerFields);

		$get = array(
			'param' 	=> 'get_param',
			'username' 	=> 'username_value',
			'password' 	=> 'password_value',
		);
	    $post = array(
			'param' => 'post_param',
		);
		$cookie = array(
			'param' => 'cookie_param',
	    );
        $env = array(
            'envParam' => 'env_value',
        );

		return new HttpRequest($get, $post, $cookie, $server, $env, array(), true);
	}

	/**
	 * Test if the getGet responses with the correct values
	 */
	public function testGetGetWithValue() {
		$this->assertSame('username_value', $this->request->getGet('username'));
		$this->assertSame('password_value', $this->request->getGet('password'));
	}

	/**
	 * Test ig the getPost responses with the correct value
	 */
	public function testGetPostWithValue() {
		$this->assertSame('post_param', $this->request->getPost('param'));
	}

	/**
	 * Test if the getCookie responses with the correct value
	 */
	public function testGetCookieWithValue() {
		$this->assertSame('cookie_param', $this->request->getCookie('param'));
	}

	/**
	 * Test if the getGet responses with the default setted value if the parameter doesn't exist.
	 */
	public function testNotExistedGetParamWithDefaultValue() {
		$this->assertSame($this->request->getGet('not_existed_param', 'default_value'), 'default_value');
	}

	/**
	 * Test if the getPost responses with the default setted value if the parameter doesn't exist.
	 */
	public function testNotExistedPostParamWithDefaultValue() {
		$this->assertSame($this->request->getPost('not_existed_param', 'default_value'), 'default_value');
	}

	/**
	 * Test if the getCookie responses with the default setted value if the parameter doesn't exist.
	 */
	public function testNotExistedCookieWithDefaultValue() {
		$this->assertSame($this->request->getCookie('not_existed_cookie_param', 'default_value'), 'default_value');
	}

	/**
	 * Test if the getParam response null if the parameter doesn't exist and no default value was given.
	 */
	public function testNotExistedParamWithoutDefaultValue() {
		$this->assertNull($this->request->getParam('not_existed_param'));
	}

	/**
	 * Tests if the getParam and setParam methods work
	 */
	public function testRouteParam() {
	    $this->request->setParam('test', 'test');
	    $this->assertEquals('test', $this->request->getParam('test'));
	}

	/**
	 * Test if the getMethod responses the right value
	 */
	public function testServerMethod() {
		$this->assertSame($this->request->getMethod(), 'post');
	}

	/**
	 * Test if the getTarget responses with the right value
	 */
	public function testTargetName() {
		$this->assertSame($this->request->getTarget(), '/target');
	}

	/**
	 * Test if it figures out if the request is ajax or not
	 */
	public function testIsAjaxRequest() {
		$this->assertFalse($this->request->isAjaxRequest());
		$request = $this->getRequest(array('HTTP_X_REQUESTED_WITH' => 'xmlhttprequest'));
		$this->assertTrue($request->isAjaxRequest());
	}

	/**
	 * Test the parameter sequence
	 */
	public function testParamSequence() {
		$this->assertSame($this->request->get('param', null, 'G'), 'get_param', 'Generic getter fails for GET');
		$this->assertSame($this->request->get('param', null, 'P'), 'post_param', 'Generic getter fails for POST');
		$this->assertSame($this->request->get('param', null, 'C'), 'cookie_param', 'Generic getter fails for COOKIE');
		$this->assertNull($this->request->get('nonexistent'), 'Generic getter fails for nonexistent value');
	}

    public function testGetServer() {
        $this->assertSame('/target', $this->request->getServer('REQUEST_URI'), 'Get from server fails');
        $this->assertNull($this->request->getServer('nonexistent'), 'Get nonexistent key from server fails');
    }

    public function testGetEnv() {
        $this->assertSame('env_value', $this->request->getEnv('envParam'), 'Get from env fails');
        $this->assertNull($this->request->getEnv('nonexistent'), 'Get nonexistent key from env fails');
    }
}