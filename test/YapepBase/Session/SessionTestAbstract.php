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
use YapepBase\Exception\ConfigException;
use YapepBase\Exception\Exception;
use YapepBase\Event\Event;
use YapepBase\Mock\Storage\StorageMock;
use YapepBase\Config;

/**
 * SimpleSessionTest class
 *
 * @package    YapepBase
 * @subpackage Test\Session
 */
abstract class SessionTestAbstract extends \YapepBase\BaseTest {

	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp() {
		parent::setUp();
		Config::getInstance()->set(array(
			'resource.session.test.namespace'    => 'test',
			'resource.session.test.cookieName'   => 'testSession',
			'resource.session.test.cookieDomain' => 'testdomain',
			'resource.session.test.cookiePath'   => '/test',

			'resource.missingNamespace.cookieName'  => 'testSession',
		));
	}

	/**
	 * Tears down the fixture, for example, close a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown() {
		Config::getInstance()->clear();
		Application::getInstance()->getDiContainer()->getEventHandlerRegistry()->clearAll();
		parent::tearDown();
	}

	/**
	 * Returns a storage mock object.
	 *
	 * @param bool  $ttlSupport   Whether TTL is supported by the storage.
	 * @param array $data         The data in the storage.
	 *
	 * @return \YapepBase\Mock\Storage\StorageMock
	 */
	protected function getStorageMock($ttlSupport = true, array $data = array()) {
		return new StorageMock($ttlSupport, false, $data);
	}

	/**
	 * Tests the array access functionality of the session.
	 *
	 * @return void
	 */
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

	/**
	 * Tests getting the namespace from the session.
	 *
	 * @return void
	 */
	public function testGetNamespace() {
		$session = $this->getSession();
		$this->assertSame('test', $session->getNamespace(), 'Namespace does not match');
	}

	/**
	 * Tests the data loading of the session when used with events.
	 *
	 * @return void
	 */
	public function testEventLoading() {
		$sessionData = array('session.test.test' => array('test' => 'testValue'));
		$storage = null;
		$session = $this->getSession('test', $sessionData);
		$session->handleEvent(new Event(Event::TYPE_APPLICATION_BEFORE_CONTROLLER_RUN));
		$this->assertSame('testValue', $session['test'], 'Loaded value is invalid');
	}

	/**
	 * Tests how the session handles invalid event handling.
	 *
	 * @return void
	 */
	public function testInvalidEventHandling() {
		$session = $this->getSession();
		$session->handleEvent(new Event(Event::TYPE_APPLICATION_BEFORE_CONTROLLER_RUN));
		$session['test'] = 'testValue';
		$session->handleEvent(new Event(Event::TYPE_APPLICATION_BEFORE_CONTROLLER_RUN));
		$this->assertSame('testValue', $session['test'], 'Loading the session twice overwrites data');
	}

	/**
	 * Tests the error handling in the session
	 *
	 * @return void
	 */
	public function testErrorHandling() {
		$storage = $this->getStorageMock();
		$nonTtlStorage = $this->getStorageMock(false);

		try {
			$this->getSession(null, array(), $nonTtlStorage);
			$this->fail('Non TTL supporting storage does not cause an exception');
		} catch (Exception $e) {
		}

		try {
			$this->getSession(null, array(), $storage, false, 'nonExisting');
			$this->fail('Non existing config does not cause an exception');
		} catch (ConfigException $exception) {
		}

		try {
			$this->getSession(null, array(), $storage, false, 'missingNamespace');
			$this->fail('No config exception thrown for missing required namespace');
		} catch (ConfigException $exception) {
		}

		try {
			$session = $this->getSession();
			$session->saveSession();
			$this->fail('No exception thrown when trying to save a not yet loaded session');
		} catch (Exception $e) {
		}
	}

	/**
	 * Tests the event handler registration
	 *
	 * @return void
	 */
	public function testEventHandlerRegistration() {
		$storage = $this->getStorageMock();
		$eventHandlerRegistry = Application::getInstance()->getDiContainer()->getEventHandlerRegistry();
		$session = $this->getSession(null, array(), $storage, true);

		$this->assertTrue(in_array($session, $eventHandlerRegistry->getEventHandlers(
				Event::TYPE_APPLICATION_BEFORE_CONTROLLER_RUN), true),
			'Autoregistration failed for APPSTART event');

		$this->assertTrue(in_array($session, $eventHandlerRegistry->getEventHandlers(
				Event::TYPE_APPLICATION_AFTER_CONTROLLER_RUN), true),
			'Autoegistration failed for APPFINISH event');

		$session->removeEventHandler();

		$this->assertFalse(in_array($session, $eventHandlerRegistry->getEventHandlers(
				Event::TYPE_APPLICATION_BEFORE_CONTROLLER_RUN), true),
			'Unregistration failed for APPSTART event');

		$this->assertFalse(in_array($session, $eventHandlerRegistry->getEventHandlers(
				Event::TYPE_APPLICATION_AFTER_CONTROLLER_RUN), true),
			'Unregistration failed for APPFINISH event');

		$session->registerEventHandler();

		$this->assertTrue(in_array($session, $eventHandlerRegistry->getEventHandlers(
				Event::TYPE_APPLICATION_BEFORE_CONTROLLER_RUN), true),
			'Manual registration failed for APPSTART event');

		$this->assertTrue(in_array($session, $eventHandlerRegistry->getEventHandlers(
				Event::TYPE_APPLICATION_AFTER_CONTROLLER_RUN), true),
			'Manual registration failed for APPFINISH event');
	}


	/**
	 * Tests how the session handles invalid session IDs.
	 *
	 * @return void
	 */
	public function testInvalidSessionHandling() {
		$session = $this->getSession('nonexistent');
		$session->loadSession();
		$this->assertNotEquals('nonexistent', $session->getId(), 'The session ID was not changed');
	}

	/**
	 * Tests the storage operation of the session when used with events.
	 *
	 * @return void
	 */
	public function testEventStorage() {
		$storage = $this->getStorageMock();
		$session = $this->getSession(null, array(), $storage);
		$session->handleEvent(new Event(Event::TYPE_APPLICATION_BEFORE_CONTROLLER_RUN));
		$session['test'] = 'testValue';
		$session->handleEvent(new Event(Event::TYPE_APPLICATION_AFTER_CONTROLLER_RUN));
		$storedData = $storage->getData();
		$this->assertSame(
			array('session.test.' . $session->getId() => array('test' => 'testValue')),
			$storedData, 'Stored data is incorrect');
	}

	/**
	 * Tests the storage operation handling of the session when used directly.
	 *
	 * @return void
	 */
	public function testDirectStorage() {
		$storage = $this->getStorageMock();
		$session = $this->getSession(null, array(), $storage);
		$session->loadSession();
		$session['test'] = 'testValue';
		$session->saveSession();
		$storedData = $storage->getData();
		$this->assertSame(
			array('session.test.' . $session->getId() => array('test' => 'testValue')),
			$storedData, 'Stored data is incorrect');
	}


	/**
	 * Returns a session instance with the most basic setup.
	 *
	 * @param string                                 $sessionId      The ID of the session.
	 * @param array                                  $sessionData    The data in the session.
	 * @param \YapepBase\Mock\Storage\StorageMock    $storage        The storage instance. (Outgoing param)
	 * @param bool                                   $autoRegister   Whether the session should auto-register.
	 * @param string                                 $configName     The configuration's name.
	 *
	 * @return ISession
	 */
	abstract protected function getSession(
		$sessionId = null, array $sessionData = array(), &$storage = null, $autoRegister = false, $configName = 'test'
	);

}