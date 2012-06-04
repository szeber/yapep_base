<?php

namespace YapepBase\Controller;
use YapepBase\Test\Mock\Router\RouterMock;
use YapepBase\Application;
use YapepBase\Exception\RedirectException;
use YapepBase\Test\Mock\Response\OutputMock;
use YapepBase\Exception\ControllerException;
use YapepBase\Test\Mock\Controller\HttpMockController;
use YapepBase\Request\HttpRequest;
use YapepBase\Test\Mock\Response\ResponseMock;
use YapepBase\Response\HttpResponse;
use YapepBase\Test\Mock\Request\RequestMock;

/**
 * Test class for HttpController.
 * Generated by PHPUnit on 2011-12-15 at 09:27:34.
 */
class HttpControllerTest extends \PHPUnit_Framework_TestCase {

	function testConstructor() {
		try {
			$request = new RequestMock('');
			$response = new HttpResponse();
			$o = new HttpMockController($request, $response);
			$this->fail('Passing a non-HTTP request to the HttpController should result in a ControllerException');
		} catch (ControllerException $e) {
			$this->assertEquals(ControllerException::ERR_INCOMPATIBLE_REQUEST, $e->getCode());
		}

		try {
			$request = new HttpRequest(array(), array(), array(), array('REQUEST_URI' => '/'), array(), array());
			$response = new ResponseMock();
			$o = new HttpMockController($request, $response);
			$this->fail('Passing a non-HTTP request to the HttpController should result in a ControllerException');
		} catch (ControllerException $e) {
			$this->assertEquals(ControllerException::ERR_INCOMPATIBLE_RESPONSE, $e->getCode());
		}

		$request = new HttpRequest(array(), array(), array(), array('REQUEST_URI' => '/'), array(), array());
		$response = new HttpResponse(new OutputMock());
		$o = new HttpMockController($request, $response);
	}

	function testRedirect() {
		$request = new HttpRequest(array(), array(), array(), array('REQUEST_URI' => '/'), array(), array());
		$out = new OutputMock();
		$response = new HttpResponse($out);
		$o = new HttpMockController($request, $response);

		try {
			$o->testRedirect();
			$this->fail('Redirect test should result in a RedirectException');
		} catch (RedirectException $e) {
			$this->assertEquals(RedirectException::TYPE_EXTERNAL, $e->getCode());
		}

		$response->send();
		$this->assertEquals(301, $out->responseCode);
		$this->assertEquals(array('http://www.example.com/'), $out->headers['Location']);
	}

	function testRedirectToRoute() {
		$router = new RouterMock();
		Application::getInstance()->setRouter($router);
		$request = new HttpRequest(array(), array(), array(), array('REQUEST_URI' => '/'), array(), array());
		$out = new OutputMock();
		$response = new HttpResponse($out);
		$o = new HttpMockController($request, $response);

		try {
			$o->testRedirectToRoute();
			$this->fail('RedirectToRoute test should result in a RedirectException');
		} catch (RedirectException $e) {
			$this->assertEquals(RedirectException::TYPE_EXTERNAL, $e->getCode());
		}

		$response->send();
		$this->assertEquals(303, $out->responseCode);
		$this->assertEquals(array('/?test=test&test2%5B0%5D=test1&test2%5B1%5D=test2#test'), $out->headers['Location']);
	}
}