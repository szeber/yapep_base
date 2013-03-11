<?php

namespace YapepBase\Controller;

use YapepBase\Exception\RedirectException;

use YapepBase\DependencyInjection\SystemContainer;
use YapepBase\Application;
use YapepBase\Mock\Controller\MockController;

class BaseControllerTest extends \YapepBase\BaseTest {

	protected $originalDiContainer;

	protected function setUp() {
		parent::setUp();
		$application               = Application::getInstance();
		$this->originalDiContainer = $application->getDiContainer();
		$diContainer = new SystemContainer();
		$diContainer->addSearchNamespace(SystemContainer::NAMESPACE_SEARCH_CONTROLLER,
			'\YapepBase\Mock\Controller');
		$application->setDiContainer($diContainer);
		$application->setI18nTranslator(new \YapepBase\Mock\I18n\TranslatorMock(
			function($sourceClass, $string, $params, $language) {
				return json_encode(array(
					'class'    => $sourceClass,
					'string'   => $string,
					'params'   => $params,
					'language' => $language,
				));
			}
		));
	}

	protected function tearDown() {
		parent::tearDown();
		$application = Application::getInstance();
		$application->setDiContainer($this->originalDiContainer);
		$application->clearI18nTranslator();
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
			$view = new \YapepBase\Mock\Response\ViewMock();
			$view->set('view test string');
			return $view;
		});
		$controller->run('test');
		$this->assertEquals('view test string', $response->getRenderedBody());

		$controller->setAction(function(MockController $controller) {
			$controller->internalRedirect('Mock', 'RedirectTarget');
		});
		try {
			$controller->run('test');
			$this->fail('No redirectException is thrown');
		} catch (RedirectException $exception) {
		}
		$this->assertEquals('redirect test', $response->getRenderedBody());
	}

	public function getController(&$response = null) {
		$request      = new \YapepBase\Mock\Request\RequestMock('http://example.com/');
		$output       = new \YapepBase\Mock\Response\OutputMock();
		$response     = new \YapepBase\Response\HttpResponse($output);
		return new \YapepBase\Mock\Controller\MockController($request, $response);
	}

	public function testSetToView() {
		$controller = $this->getController();
		$controller->setAction(function(MockController $instance) {
			$instance->setToView('test', 'test value');
		});
		$controller->run('test');
		$this->assertEquals('test value', Application::getInstance()->getDiContainer()->getViewDo()->get('test'));
	}

	public function testTranslation() {
		$controller = $this->getController();
		$expectedResult = array(
			'class' => 'YapepBase\Mock\Controller\MockController',
			'string' => 'test',
			'params' => array('testParam' => 'testValue'),
			'language' => 'en',
		);
		$this->assertSame($expectedResult, json_decode($controller->_('test', array('testParam' => 'testValue'), 'en'),
			true), 'The translator method does not return the expected result');
	}
}
