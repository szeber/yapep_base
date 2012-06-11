<?php

namespace YapepBase\DependencyInjection;

use YapepBase\DependencyInjection\SystemContainer;

class SystemContainerTest extends \PHPUnit_Framework_TestCase {
	public function testConstructor() {
		$sc = new SystemContainer();
		$this->assertInstanceOf('\YapepBase\ErrorHandler\ErrorHandlerRegistry', $sc->getErrorHandlerRegistry());
		$this->assertInstanceOf('\YapepBase\Log\Message\ErrorMessage', $sc->getErrorLogMessage());
		$this->assertInstanceOf('\YapepBase\Event\EventHandlerRegistry', $sc->getEventHandlerRegistry());
		$this->assertInstanceOf('\YapepBase\Session\SessionRegistry', $sc->getSessionRegistry());
	}

	public function testGetMemcache() {
		if (!class_exists('\Memcache')) {
			$this->markTestSkipped('No memcache support');
		}
		$sc = new SystemContainer();
		$this->assertInstanceOf('\Memcache', $sc->getMemcache());
	}

	public function testGetMemcached() {
		if (!class_exists('\Memcached')) {
			$this->markTestSkipped('No memcached support');
		}
		$sc = new SystemContainer();
		$this->assertInstanceOf('\Memcached', $sc->getMemcached());
	}

	public function testGetController() {
		$sc = new SystemContainer();
		$sc->setSearchNamespaces(SystemContainer::NAMESPACE_SEARCH_CONTROLLER, array());
		try {
			$request = new \YapepBase\Mock\Request\RequestMock('', '');
			$response = new \YapepBase\Mock\Response\ResponseMock();
			$sc->getController('Mock', $request, $response);
			$this->fail('Getting a controller with an empty search array should result in a ControllerException');
		} catch (\YapepBase\Exception\ControllerException $e) {
			$this->assertEquals(\YapepBase\Exception\ControllerException::ERR_CONTROLLER_NOT_FOUND, $e->getCode());
		}
		$sc->addSearchNamespace(SystemContainer::NAMESPACE_SEARCH_CONTROLLER, '\YapepBase\Mock\Controller');
		$this->assertInstanceOf('\YapepBase\Controller\BaseController', $sc->getController('Mock', $request, $response));
	}

	public function testGetTemplate() {
		$sc = new SystemContainer();
		$sc->setSearchNamespaces(SystemContainer::NAMESPACE_SEARCH_TEMPLATE, array());
		try {
			$sc->getTemplate('Mock');
			$this->fail('Getting a template with an empty search array should result in a ViewException');
		} catch (\YapepBase\Exception\ViewException $e) {
			$this->assertEquals(\YapepBase\Exception\ViewException::ERR_TEMPLATE_NOT_FOUND, $e->getCode());
		}
		$sc->addSearchNamespace(SystemContainer::NAMESPACE_SEARCH_TEMPLATE, '\YapepBase\Mock\View');
		$this->assertInstanceOf('\YapepBase\View\TemplateAbstract', $sc->getTemplate('Mock'));
	}

	public function testBo() {

	}

	public function testDao() {

	}

	public function testMiddlewareStorage() {

	}

	public function testDefaultErrorController() {

	}

	public function testLoggerRegistry() {

	}

	public function testDebugger() {

	}
}
