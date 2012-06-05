<?php

namespace YapepBase\Controller;

use YapepBase\Exception\RedirectException;

use YapepBase\DependencyInjection\SystemContainer;
use YapepBase\Application;

class BaseControllerTest extends \PHPUnit_Framework_TestCase {

	protected $originalDiContainer;

	protected function setUp() {
		parent::setUp();
		$this->originalDiContainer = Application::getInstance()->getDiContainer();
		$diContainer = new SystemContainer();
		$diContainer->addSearchNamespace(SystemContainer::NAMESPACE_SEARCH_CONTROLLER,
			'\YapepBase\Test\Mock\Controller');
		Application::getInstance()->setDiContainer($diContainer);
	}

	protected function tearDown() {
		parent::tearDown();
		Application::getInstance()->setDiContainer($this->originalDiContainer);
	}

	public function testRun() {
		$response = null;
		$controller = $this->getController($response);

		$controller->setAction(function() {});
		$this->assertFalse($controller->ran);
		$controller->run('test');
		$this->assertTrue($controller->ran);

		try {
			$controller->run('nonExistent');
			$this->fail('Running a non-existent action should result in a ControllerException');
		} catch (\YapepBase\Exception\ControllerException $e) {
			$this->assertEquals(\YapepBase\Exception\ControllerException::ERR_ACTION_NOT_FOUND, $e->getCode());
		}

		$controller->setAction(function() {
			return 3.14;
		});
		try {
			$controller->run('test');
			$this->fail('Running an action with an invalid result in a ControllerException');
		} catch (\YapepBase\Exception\ControllerException $e) {
			$this->assertEquals(\YapepBase\Exception\ControllerException::ERR_INVALID_ACTION_RESULT, $e->getCode());
		}

		$controller->setAction(function() {
			return 'test string';
		});
		$controller->run('test');
		$this->assertEquals('test string', $response->getRenderedBody());

		$controller->setAction(function() {
			$view = new \YapepBase\Test\Mock\Response\ViewMock();
			$view->set('view test string');
			return $view;
		});
		$controller->run('test');
		$this->assertEquals('view test string', $response->getRenderedBody());

		try {
			$controller->run('redirect');
			$this->fail('No redirectException is thrown');
		} catch (RedirectException $exception) {
		}
		$this->assertEquals('redirect test', $response->getRenderedBody());
	}

	public function getController(&$response = null) {
		$request      = new \YapepBase\Test\Mock\Request\RequestMock('http://example.com/');
		$output       = new \YapepBase\Test\Mock\Response\OutputMock();
		$response     = new \YapepBase\Response\HttpResponse($output);
		return new \YapepBase\Test\Mock\Controller\ControllerMock($request, $response);
	}

	public function testSetToView() {
		$controller = $this->getController();
		$controller->setAction(function(BaseController $instance) {
			$instance->setToView('test', 'test value');
		});
		$controller->run('test');
		$this->assertEquals('test value', Application::getInstance()->getDiContainer()->getViewDo()->get('test'));
	}
}
