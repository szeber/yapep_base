<?php

namespace YapepBase\Test;

use YapepBase\Request\HttpRequest;

require_once dirname(__FILE__) . '/../bootstrap.php';

class HttpRequestTest extends \PHPUnit_Framework_TestCase {
	/**
	 * The request
	 *
	 * @var \YapepBase\Request\HttpRequest
	 */
	protected $request;

	protected function setUp() {

		parent::setUp();

		$_SERVER['REQUEST_URI'] = '/target';

		$_GET = array(
			'param' 	=> 'get_param',
			'username' 	=> 'username_value',
			'password' 	=> 'password_value'
		);
		$_POST['param'] = 'post_param';
		$_COOKIE['param'] = 'cookie_param';

		$this->request = new HttpRequest();


	}

	protected function tearDown() {
		unset($_SERVER['REQUEST_URI']);
		$_GET = array();
		$_GET = array();
		$_COOKIE = array();
		parent::tearDown();
	}

	/**
	 * Test if the getGet responses with the correct values
	 *
	 * @author bpinter
	 */
	public function testGetGetWithValue() {
		$this->assertSame('username_value', $this->request->getGet('username'));
		$this->assertSame('password_value', $this->request->getGet('password'));
	}

	/**
	 * Test ig the getPost responses with the correct value
	 *
	 * @author bpinter
	 */
	public function testGetPostWithValue() {
		$this->assertSame('post_param', $this->request->getPost('param'));
	}

	/**
	 * Test if the getCookie responses with the correct value
	 *
	 * @author bpinter
	 */
	public function testGetCookieWithValue() {
		$this->assertSame('cookie_param', $this->request->getCookie('param'));
	}

	/**
	 * Test if the getGet responses with the default setted value if the parameter doesn't exist.
	 *
	 * @author bpinter
	 */
	public function testNotExistedGetParamWithDefaultValue() {
		$this->assertSame($this->request->getGet('not_existed_param', 'default_value'), 'default_value');
	}

	/**
	 * Test if the getPost responses with the default setted value if the parameter doesn't exist.
	 *
	 * @author bpinter
	 */
	public function testNotExistedPostParamWithDefaultValue() {
		$this->assertSame($this->request->getPost('not_existed_param', 'default_value'), 'default_value');
	}

	/**
	 * Test if the getCookie responses with the default setted value if the parameter doesn't exist.
	 *
	 * @author bpinter
	 */
	public function testNotExistedCookieWithDefaultValue() {
		$this->assertSame($this->request->getCookie('not_existed_cookie_param', 'default_value'), 'default_value');
	}

	/**
	 * Test if the getParam response null if the parameter doesn't exist and no default value was given.
	 *
	 * @author bpinter
	 */
	public function testNotExistedParamWithoutDefaultValue() {
		$this->assertNull($this->request->getParam('not_existed_param'));
	}

	/**
	 * Test if the getMethod responses the right value
	 *
	 * @author bpinter
	 */
	public function testServerMethod() {
		$_SERVER['REQUEST_METHOD'] = 'post';
		$this->assertSame($this->request->getMethod(), 'post');
	}

	/**
	 * Test if the getTarget responses with the right value
	 *
	 * @author bpinter
	 */
	public function testTargetName() {
		$this->assertSame($this->request->getTarget(), '/target');
	}

	/**
	 * Test if it figures out if the request is ajax or not
	 *
	 * @author bpinter
	 */
	public function testIsAjaxRequest() {
		$this->assertFalse($this->request->isAjaxRequest());
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
		$this->assertTrue($this->request->isAjaxRequest());
	}

	/**
	 * Test the parameter sequence
	 *
	 * @author bpinter
	 */
	public function testParamSequence() {
		$this->assertSame($this->request->get('param', null, 'G'), 'get_param');

	}
}