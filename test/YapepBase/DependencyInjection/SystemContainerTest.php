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

	public function testGetBo() {
		$sc = new SystemContainer();
		try {
			$sc->getBo('Mock');
			$this->fail('Getting a BO with an empty search array should result a DiException');
		} catch (\YapepBase\Exception\DiException $e) {
			$this->assertEquals(\YapepBase\Exception\DiException::ERR_NAMESPACE_SEARCH_CLASS_NOT_FOUND, $e->getCode());
		}
		$sc->addSearchNamespace(SystemContainer::NAMESPACE_SEARCH_BO, '\YapepBase\Mock\BusinessObject');
		$this->assertInstanceOf('\YapepBase\BusinessObject\BoAbstract', $sc->getBo('Mock'));
	}

	public function testGetDao() {
		$sc = new SystemContainer();
		try {
			$sc->getDao('Mock');
			$this->fail('Getting a Validator with an empty search array should result a DiException');
		} catch (\YapepBase\Exception\DiException $e) {
			$this->assertEquals(\YapepBase\Exception\DiException::ERR_NAMESPACE_SEARCH_CLASS_NOT_FOUND, $e->getCode());
		}
		$sc->addSearchNamespace(SystemContainer::NAMESPACE_SEARCH_VALIDATOR, '\YapepBase\Mock\Validator');
		$this->assertInstanceOf('\YapepBase\Validator\ValidatorAbstract', $sc->getValidator('Mock'));
	}

	/**
	 * Tests the getValidator() method.
	 *
	 * @return void
	 */
	public function testGetValidator() {
		$sc = new SystemContainer();
		try {
			$sc->getValidator('Mock');
			$this->fail('Getting a DAO with an empty search array should result a DiException');
		} catch (\YapepBase\Exception\DiException $e) {
			$this->assertEquals(\YapepBase\Exception\DiException::ERR_NAMESPACE_SEARCH_CLASS_NOT_FOUND, $e->getCode());
		}
		$sc->addSearchNamespace(SystemContainer::NAMESPACE_SEARCH_DAO, '\YapepBase\Mock\Dao');
		$this->assertInstanceOf('\YapepBase\Dao\DaoAbstract', $sc->getDao('Mock'));
	}

	public function testMiddlewareStorage() {
		$sc = new SystemContainer();
		try {
			$sc->getMiddlewareStorage();
			$this->fail('Getting a middleware storage without setting one first should result in a DiExceptiom');
		} catch (\YapepBase\Exception\DiException $e) {
			$this->assertEquals(\YapepBase\Exception\DiException::ERR_INSTANCE_NOT_SET, $e->getCode());
		}

		$storage = new \YapepBase\Mock\Storage\StorageMock(true, true);
		$sc->setMiddlewareStorage($storage);

		$this->assertSame($storage, $sc->getMiddlewareStorage(),
			'The retrieved middleware storage is not the one that has been set.');
	}

	public function testDefaultErrorController() {
		$sc = new SystemContainer();
		$controller = $sc->getDefaultErrorController(
			new \YapepBase\Request\HttpRequest(array(), array(), array(), array('REQUEST_URI' => '/'), array(),
				array(), array()),
			new \YapepBase\Response\HttpResponse(new \YapepBase\Mock\Response\OutputMock()));

		$this->assertInstanceOf('\YapepBase\Controller\DefaultErrorController', $controller,
			'The retrieved error controller is of invalid type');
	}

	public function testLoggerRegistry() {
		$sc = new SystemContainer();
		$this->assertInstanceOf('\YapepBase\Log\LoggerRegistry', $sc->getLoggerRegistry(),
			'The retrieved logger registry is of the wrong type');
	}

	public function testDebugger() {
		$sc = new SystemContainer();
		$this->assertFalse($sc->getDebugger(), 'The getDebugger method should return FALSE if no debugger is set');

		$debugger = new \YapepBase\Mock\Debugger\DebuggerMock();
		$sc->setDebugger($debugger);

		$this->assertSame($debugger, $sc->getDebugger(), 'The retrieved debugger is not the same instance');
	}
}
