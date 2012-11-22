<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Test\Session
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Session;
use YapepBase\Application;
use YapepBase\Session\SessionRegistry;
use YapepBase\Exception\ConfigException;
use YapepBase\Exception\Exception;
use YapepBase\Mock\Response\ResponseMock;
use YapepBase\Event\Event;
use YapepBase\Mock\Response\OutputMock;
use YapepBase\Response\HttpResponse;
use YapepBase\Request\HttpRequest;
use YapepBase\Mock\Request\RequestMock;
use YapepBase\Mock\Storage\StorageMock;
use YapepBase\Config;
use YapepBase\Session\HttpSession;

/**
 * HttpSessionTest class
 *
 * @package    YapepBase
 * @subpackage Test\Session
 */
class HttpSessionTest extends \PHPUnit_Framework_TestCase {

	public function setUp() {
		parent::setUp();
		Config::getInstance()->set(array(
			'resource.session.test.namespace'    => 'test',
			'resource.session.test.cookieName'   => 'testSession',
			'resource.session.test.cookieDomain' => 'testdomain',
			'resource.session.test.cookiePath'   => '/test',

			'resource.missingNamespace.cookieName'  => 'testSession',

			'resource.missingCookieName.namespace'  => 'test2',

		));
	}

	public function tearDown() {
		Config::getInstance()->clear();
		Application::getInstance()->getDiContainer()->getEventHandlerRegistry()->clearAll();
		parent::tearDown();
	}

	protected function getStorageMock($ttlSupport = true, array $data = array()) {
		return new StorageMock($ttlSupport, false, $data);
	}

	/**
	 * Instatiates a HttpSession
	 *
	 * @param string                                      $sessionId
	 * @param array                                       $sessionData
	 * @param \YapepBase\Mock\Response\OutputMock    $output
	 * @param \YapepBase\Response\HttpResponse            $response
	 * @param \YapepBase\Mock\Storage\StorageMock    $storage
	 *
	 * @return \YapepBase\Session\HttpSession
	 */
	protected function getSession($sessionId = null, $sessionData = array(), &$output = null, &$response = null, &$storage = null) {
		$storage = new StorageMock(true, false, $sessionData);
		$cookie = ($sessionId ? array('testSession' => $sessionId) : array());
		$request = new HttpRequest(array(), array(), $cookie, array('REQUEST_URI' => '/'), array(), array());
		$output = new OutputMock();
		$response = new HttpResponse($output);
		return new HttpSession('test', $storage, $request, $response, false);
	}

	public function testArrayAccess() {
		$session = $this->getSession();
		$this->assertFalse(isset($session['test']), 'Test key is set for new empty session');
		$this->assertNull($session['test'], 'Not set key is not null');
		$session['test'] = 'testValue';
		$this->assertTrue(isset($session['test']), 'Test key is not set');
		$this->assertSame('testValue', $session['test'], 'Test key is not correctly set');
		unset($session['test']);
		$this->assertFalse(isset($session['test']), 'Test key is set after deleting');
		$this->assertNull($session['test'], 'Deleted key is not null');
	}

	public function testGetNamespace() {
		$session = $this->getSession();
		$this->assertSame('test', $session->getNamespace(), 'Namespace does not match');
	}

	public function testStorage() {
		$output = $response =  $storage = null;
		$session = $this->getSession(null, array(), $output, $response, $storage);
		$session->handleEvent(new Event(Event::TYPE_APPSTART));
		$session['test'] = 'testValue';
		$session->handleEvent(new Event(Event::TYPE_APPFINISH));
		$response->send();
		$storedData = $storage->getData();
		$this->assertSame(
			array('session.test.' . $output->cookies['testSession']['value'] => array('test' => 'testValue')),
			$storedData, 'Stored data is incorrect');
	}

	public function testLoading() {
		$sessionData = array('session.test.test' => array('test' => 'testValue'));
		$output = $response =  $storage = null;
		$session = $this->getSession('test', $sessionData);
		$session->handleEvent(new Event(Event::TYPE_APPSTART));
		$this->assertSame('testValue', $session['test'], 'Loaded value is invalid');
	}

	public function testInvalidEventHandling() {
		$session = $this->getSession();
		$session->handleEvent(new Event(Event::TYPE_APPSTART));
		$session['test'] = 'testValue';
		$session->handleEvent(new Event(Event::TYPE_APPSTART));
		$this->assertSame('testValue', $session['test'], 'Loading the session twice overwrites data');
	}

	public function testInvalidSessionHandling() {
		$storage = $this->getStorageMock();
		$request = new HttpRequest(array(), array(), array('testSession' => 'nonexistent'),
			array('REQUEST_URI' => '/'), array(), array());
		$output = new OutputMock();
		$response = new HttpResponse($output);

		$session = new HttpSession('test', $storage, $request, $response, false);

		$session->handleEvent(new Event(Event::TYPE_APPSTART));

		$this->assertNotEquals('nonexistent', $session->getId(), 'The session ID was not changed');
	}

	public function testEventHandlerRegistration() {
		$output = new OutputMock();
		$storage = $this->getStorageMock();
		$request = new HttpRequest(array(), array(), array(), array('REQUEST_URI' => '/'), array(), array());
		$response = new HttpResponse($output);
		$eventHandlerRegistry = Application::getInstance()->getDiContainer()->getEventHandlerRegistry();
		$session = new HttpSession('test', $storage, $request, $response, true);

		$this->assertTrue(in_array($session, $eventHandlerRegistry->getEventHandlers(Event::TYPE_APPSTART), true),
			'Autoregistration failed for APPSTART event');

		$this->assertTrue(in_array($session, $eventHandlerRegistry->getEventHandlers(Event::TYPE_APPFINISH), true),
			'Autoegistration failed for APPFINISH event');

		$session->removeEventHandler();

		$this->assertFalse(in_array($session, $eventHandlerRegistry->getEventHandlers(Event::TYPE_APPSTART), true),
			'Unregistration failed for APPSTART event');

		$this->assertFalse(in_array($session, $eventHandlerRegistry->getEventHandlers(Event::TYPE_APPFINISH), true),
			'Unregistration failed for APPFINISH event');

		$session->registerEventHandler();

		$this->assertTrue(in_array($session, $eventHandlerRegistry->getEventHandlers(Event::TYPE_APPSTART), true),
			'Manual registration failed for APPSTART event');

		$this->assertTrue(in_array($session, $eventHandlerRegistry->getEventHandlers(Event::TYPE_APPFINISH), true),
			'Manual registration failed for APPFINISH event');
	}

	public function testErrorHandling() {
		$output = new OutputMock();
		$storage = $this->getStorageMock();
		$nonTtlStorage = $this->getStorageMock(false);
		$request = new HttpRequest(array(), array(), array(), array('REQUEST_URI' => '/'), array(), array());
		$response = new HttpResponse($output);
		$requestMock = new RequestMock('test');
		$responseMock = new ResponseMock();

		try {
			new HttpSession('test', $nonTtlStorage, $request, $response);
			$this->fail('Non TTL supporting storage does not cause an exception');
		} catch (Exception $e) {
		}

		try {
			new HttpSession('nonExisting', $storage, $request, $response);
			$this->fail('Non existing config does not cause an exception');
		} catch (ConfigException $exception) {
		}

		try {
			new HttpSession('missingCookieName', $storage, $request, $response);
			$this->fail('No config exception thrown for missing required cookie name');
		} catch (ConfigException $exception) {
		}

		try {
			new HttpSession('missingNamespace', $storage, $request, $response);
			$this->fail('No config exception thrown for missing required namespace');
		} catch (ConfigException $exception) {
		}

		try {
			new HttpSession('test', $storage, $requestMock, $response);
			$this->fail('No exception thrown for non HttpRequest request instance');
		} catch (Exception $exception) {
		}


		try {
			new HttpSession('test', $storage, $request, $responseMock);
			$this->fail('No exception thrown for non HttpResponse response instance');
		} catch (Exception $exception) {
		}

		try {
			$session = new HttpSession('test', $storage, $request, $response);
			$session->handleEvent(new Event(Event::TYPE_APPFINISH));
			$this->fail('No exception thrown when trying to save a not yet loaded session');
		} catch (Exception $e) {
		}
	}
}