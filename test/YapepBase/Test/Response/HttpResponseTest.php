<?php

namespace YapepBase\Test\Response;

use YapepBase\Response\HttpResponse;
use YapepBase\Config;

class HttpResponseTest extends \PHPUnit_Framework_TestCase {
	/**
	 * @var \YapepBase\Test\Mock\Response\OutputMock
	 */
	protected $output;

	/**
	 * The request
	 *
	 * @var \YapepBase\Response\HttpResponse
	 */
	protected $response;

	/**
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	protected function setUp() {

		parent::setUp();

		$this->output = new \YapepBase\Test\Mock\Response\OutputMock();
		$this->createCleanResponse();
	}

	/**
	 * This function creates a clean HttpResponse instance.
	 */
	protected function createCleanResponse() {
		$this->output->clean();
		$this->response = new HttpResponse($this->output);
	}

	/**
	 * This function tests, that bodies are correctly stored and rendered
	 */
	public function testBodyStoringAndRendering() {

		Config::getInstance()->clear();
		Config::getInstance()->set('system.response.gzip', false);

		$this->response->setRenderedBody('test body 1');
		$this->assertEquals('test body 1', $this->response->getRenderedBody(),
			'HttpResponse does not correctly store or render a plain text body.');

		$view = new \YapepBase\Test\Mock\Response\ViewMock();
		$view->set('test body 2');
		$this->response->setBody($view);
		$this->assertEquals('test body 2', $this->response->getRenderedBody(),
			'HttpResponse does not correctly store or render an ViewAbstract body.');
	}

	/**
	 * Tests, if a response can be sent twice.
	 */
	public function testResponseSent() {
		$this->createCleanResponse();
		$this->response->send();
		try {
			$this->response->send();
			$this->fail('A response can be sent twice in HttpResponse::send');
		} catch (\YapepBase\Exception\Exception $e) { }

		$this->createCleanResponse();
		$this->response->sendError();
		try {
			$this->response->sendError();
			$this->fail('A response can be sent twice in HttpResponse::sendError');
		} catch (\YapepBase\Exception\Exception $e) { }
	}

	/**
	 * @covers \YapepBase\Response\HttpResponse::checkStandards
	 */
	public function testCheckStandards() {
		/**
		 * Check HTTP 204
		 */
		try {
			$this->createCleanResponse();
			$this->response->setStatusCode(204);
			$this->response->setRenderedBody('test');
			$this->response->send();
			$this->fail('Standards compliancy test should fail with status code 204 and non-empty body.');
		} catch (\YapepBase\Exception\StandardsComplianceException $e) {}
		$this->createCleanResponse();
		$this->response->setStatusCode(204);
		$this->response->setRenderedBody('');
		$this->response->send();

		/**
		 * Check HTTP 206
		 */
		try {
			$this->createCleanResponse();
			$this->response->setStatusCode(206);
			$this->response->send();
			$this->fail('Standards compliancy test should fail with status code 206 and missing Date and Content-Range headers.');
		} catch (\YapepBase\Exception\StandardsComplianceException $e) {}
		try {
			$this->createCleanResponse();
			$this->response->setStatusCode(206);
			$this->response->setHeader('Date', date('r'));
			$this->response->send();
			$this->fail('Standards compliancy test should fail with status code 206 and missing Content-Range headers.');
		} catch (\YapepBase\Exception\StandardsComplianceException $e) {}
		try {
			$this->createCleanResponse();
			$this->response->setStatusCode(206);
			$this->response->setHeader('Content-Range', 'bytes 21010-47021/47022');
			$this->response->send();
			$this->fail('Standards compliancy test should fail with status code 206 and missing Date headers.');
		} catch (\YapepBase\Exception\StandardsComplianceException $e) {}
		$this->createCleanResponse();
		$this->response->setStatusCode(206);
		$this->response->setHeader('Date', date('r'));
		$this->response->setHeader('Content-Range', 'bytes 21010-47021/47022');
		$this->response->send();

		/**
		 * Check HTTP 301, 302, 303 and 307
		 */
		foreach (array(301, 302, 303, 307) as $statuscode) {
			try {
				$this->createCleanResponse();
				$this->response->setStatusCode($statuscode);
				$this->response->send();
				$this->fail('Standards compliancy test should fail with status code ' . $statuscode . ' and no Location setHeader.');
			} catch (\YapepBase\Exception\StandardsComplianceException $e) {}
			$this->createCleanResponse();
			$this->response->setStatusCode($statuscode);
			$this->response->setHeader('Location', 'http://example.com/');
			$this->response->send();
		}

		/**
		 * Check HTTP 304
		 */
		try {
			$this->createCleanResponse();
			$this->response->setStatusCode(304);
			$this->response->send();
			$this->fail('Standards compliancy test should fail with status code 304 and missing Date setHeader.');
		} catch (\YapepBase\Exception\StandardsComplianceException $e) {}
		$this->createCleanResponse();
		$this->response->setStatusCode(304);
		$this->response->setHeader('Date', date('r'));
		$this->response->send();

		/**
		 * Check HTTP 401
		 */
		try {
			$this->createCleanResponse();
			$this->response->setStatusCode(401);
			$this->response->send();
			$this->fail('Standards compliancy test should fail with status code 401 and missing WWW-Authenticate setHeader.');
		} catch (\YapepBase\Exception\StandardsComplianceException $e) {}
		$this->createCleanResponse();
		$this->response->setStatusCode(401);
		$this->response->setHeader('WWW-Authenticate', 'abc123');
		$this->response->send();

		/**
		 * Check HTTP 405
		 */
		try {
			$this->createCleanResponse();
			$this->response->setStatusCode(405);
			$this->response->send();
			$this->fail('Standards compliancy test should fail with status code 405 and missing Allow setHeader.');
		} catch (\YapepBase\Exception\StandardsComplianceException $e) {}
		$this->createCleanResponse();
		$this->response->setStatusCode(405);
		$this->response->setHeader('Allow', 'GET, HEAD, PUT');
		$this->response->send();
	}

	/**
	 * Tests the correct handing of headers.
	 */
	public function testHeaders() {
		$this->createCleanResponse();

		$this->assertEquals(false, $this->response->hasHeader('X-Test-Header'));
		$this->response->setHeader('X-Test-Header', 'Test Value');
		$this->assertEquals(true, $this->response->hasHeader('X-Test-Header'));
		$this->assertEquals(array('Test Value'), $this->response->getHeader('X-Test-Header'));
		$this->response->addHeader('X-Test-Header', 'Test Value 2');
		$this->assertEquals(array('Test Value', 'Test Value 2'), $this->response->getHeader('X-Test-Header'));
		$this->response->setHeader('X-Test-Header', 'Test Value 3');
		$this->assertEquals(array('Test Value 3'), $this->response->getHeader('X-Test-Header'));
		$this->response->removeHeader('X-Test-Header');
		$this->assertEquals(false, $this->response->hasHeader('X-Test-Header'));

		$this->createCleanResponse();

		$this->response->setHeader('X-Test-Header-2: Test : Value');
		$this->assertEquals(array('Test : Value'), $this->response->getHeader('X-Test-Header-2'));

		$this->createCleanResponse();
		$this->response->setHeader(array('X-Test-Header-3: Test : Value', 'X-Test-Header-4: Test : Value'));
		$this->assertEquals(array('Test : Value'), $this->response->getHeader('X-Test-Header-3'));
		$this->assertEquals(array('Test : Value'), $this->response->getHeader('X-Test-Header-4'));

		$this->createCleanResponse();
		$this->response->setHeader(array('X-Test-Header-3' => 'Test : Value', 'X-Test-Header-4' => 'Test : Value'));
		$this->assertEquals(array('Test : Value'), $this->response->getHeader('X-Test-Header-3'));
		$this->assertEquals(array('Test : Value'), $this->response->getHeader('X-Test-Header-4'));

		$this->createCleanResponse();
		try {
			$this->response->addHeader('This is an invalid setHeader line');
			$this->fail('Invalid setHeader lines are not caught!');
		} catch (\YapepBase\Exception\ParameterException $e) {}

		try {
			$this->response->addHeader('');
			$this->fail('Empty setHeader lines are not caught!');
		} catch (\YapepBase\Exception\ParameterException $e) {}

		try {
			$this->response->addHeader('X-Test-Header', '');
			$this->fail('Empty setHeader values are not caught!');
		} catch (\YapepBase\Exception\ParameterException $e) {}

		try {
			$this->response->getHeader('X-Non-Existent');
			$this->fail('Fetching non-existent headers should throw an IndexOutOfBoundsException');
		} catch (\YapepBase\Exception\IndexOutOfBoundsException $e) {}
	}

	/**
	 * Tests the correct handling of cookies.
	 */
	public function testCookies() {
		$this->createCleanResponse();

		$data = array('name' => 'testcookie', 'value' => 'testvalue', 'expire' => 3600, 'path' => '/test', 'domain' => 'www.example.com', 'secure' => true, 'httponly' => true);

		$this->assertEquals(array(), $this->output->cookies, 'The cookies array is not empty in OutputMock. Possibly a bug in HttpResponseTest::createCleanResponse?');
		$this->assertEquals(false, $this->response->hasCookie($data['name']));
		$this->response->setCookie($data['name'], $data['value'], $data['expire'], $data['path'], $data['domain'], $data['secure'], $data['httponly']);
		$this->assertEquals(true, $this->response->hasCookie($data['name']));
		$this->response->send();
		$this->assertEquals(array($data['name'] => $data), $this->output->cookies);

		$this->createCleanResponse();
		$data = array('name' => 'testcookie', 'value' => 'testvalue', 'expire' => 3600, 'path' => '/test', 'domain' => 'www.example.org', 'secure' => true, 'httponly' => true);
		Config::getInstance()->set('system.response.defaultCookieDomain', $data['domain']);
		$this->response->setCookie($data['name'], $data['value'], $data['expire'], $data['path'], null, $data['secure'], $data['httponly']);
		$this->response->send();
		$this->assertEquals(array($data['name'] => $data), $this->output->cookies);
	}

	/**
	 * Tests, that the sendError method sends a HTTP 500 error
	 */
	public function testError() {
		$this->createCleanResponse();

		$this->response->sendError();
		$this->assertEquals(500, $this->output->responseCode, 'HttpResponse::sendError should send a HTTP 500 status code.');
	}

	/**
	 * Tests the correct handling of status codes.
	 */
	public function testStatusCode() {
		$this->createCleanResponse();

		$this->response->setStatusCode(201);
		$this->assertEquals(201, $this->response->getStatusCode());
		$this->assertNotEmpty($this->response->getStatusMessage());

		$this->response->setStatusCode(600);
		$this->assertEquals(600, $this->response->getStatusCode());
		$this->assertNotEmpty($this->response->getStatusMessage());
	}

	/**
	 * Tests, that the redirect function throws an exception and correctly
	 * passes the parameters.
	 */
	public function testRedirect() {
		$this->createCleanResponse();
		try {
			$this->response->redirect('http://www.example.com/', 301);
			$this->fail('HttpResponse::redirect should throw a RedirectException');
		} catch (\YapepBase\Exception\RedirectException $e) {}
		$this->response->send();
		$this->assertEquals(301, $this->output->responseCode);
		$this->assertEquals(array('http://www.example.com/'), $this->output->headers['Location']);
	}
}