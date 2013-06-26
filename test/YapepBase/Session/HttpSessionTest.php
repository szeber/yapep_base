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
use YapepBase\Mock\Response\OutputMock;
use YapepBase\Response\HttpResponse;
use YapepBase\Request\HttpRequest;
use YapepBase\Mock\Storage\StorageMock;
use YapepBase\Config;
use YapepBase\Session\HttpSession;

/**
 * HttpSessionTest class
 *
 * @package    YapepBase
 * @subpackage Test\Session
 */
class HttpSessionTest extends SessionTestAbstract {

	protected $originalObLevel;

	protected function setUp() {
		parent::setUp();
		$this->originalObLevel = ob_get_level();
		Config::getInstance()->set(array(
			'resource.missingCookieName.namespace'  => 'test2',
		));
	}

	protected function tearDown() {
		parent::tearDown();
		while (ob_get_level() > $this->originalObLevel) {
			ob_end_flush();
		}
	}

	/**
	 * Instantiates a HttpSession
	 *
	 * @param string                                 $sessionId      The session ID.
	 * @param array                                  $sessionData    The session data.
	 * @param \YapepBase\Mock\Response\OutputMock    $output         The output instance. (Outgoing param)
	 * @param \YapepBase\Response\HttpResponse       $response       The response instance. (Outgoing param)
	 * @param \YapepBase\Mock\Storage\StorageMock    $storage        The storage instance. (Outgoing param)
	 * @param bool                                   $autoRegister   Whether the session should auto-register.
	 * @param string                                 $configName     The configuration's name.
	 *
	 * @return \YapepBase\Session\HttpSession
	 */
	protected function getHttpSession(
		$sessionId = null, array $sessionData = array(), &$output = null, &$response = null, &$storage = null,
		$autoRegister = false, $configName = 'test', $cookieName = 'testSession'
	) {
		$cookie = ($sessionId ? array($cookieName => $sessionId) : array());
		$request = new HttpRequest(array(), array(), $cookie, array('REQUEST_URI' => '/'), array(), array());

		$storage = empty($storage) ? $this->getStorageMock(true, $sessionData) : $storage;

		$output = empty($output) ? new OutputMock() : $output;

		$response = empty($response) ? new HttpResponse($output) : $response;

		return new HttpSession($configName, $storage, $request, $response, $autoRegister);
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
	protected function getSession(
		$sessionId = null, array $sessionData = array(), &$storage = null, $autoRegister = false, $configName = 'test'
	) {
		$output = null;
		$response = null;
		return $this->getHttpSession($sessionId, $sessionData, $output, $response, $storage, $autoRegister, $configName);
	}

	/**
	 * Tests the storage operation and cookie handling of the session when used with events.
	 *
	 * @return void
	 */
	public function testEventStorage() {
		parent::testEventStorage();
		/** @var \YapepBase\Mock\Response\OutputMock $output */
		$output = null;
		/** @var \YapepBase\Response\HttpResponse $response */
		$response = null;
		/** @var \YapepBase\Mock\Storage\StorageMock $storage */
		$storage = null;
		$session = $this->getHttpSession(null, array(), $output, $response, $storage);
		$session->handleEvent(new Event(Event::TYPE_APPLICATION_BEFORE_CONTROLLER_RUN));
		$session['test'] = 'testValue';
		$session->handleEvent(new Event(Event::TYPE_APPLICATION_AFTER_CONTROLLER_RUN));
		$response->send();
		$storedData = $storage->getData();
		$this->assertSame(
			array('session.test.' . $output->cookies['testSession']['value'] => array('test' => 'testValue')),
			$storedData, 'Stored data is incorrect');
	}

	/**
	 * Tests the storage operation and cookie handling of the session when used directly.
	 *
	 * @return void
	 */
	public function testDirectStorage() {
		parent::testDirectStorage();
		/** @var \YapepBase\Mock\Response\OutputMock $output */
		$output = null;
		/** @var \YapepBase\Response\HttpResponse $response */
		$response = null;
		/** @var \YapepBase\Mock\Storage\StorageMock $storage */
		$storage = null;
		$session = $this->getHttpSession(null, array(), $output, $response, $storage);
		$session->loadSession();
		$session['test'] = 'testValue';
		$session->saveSession();
		$response->send();
		$storedData = $storage->getData();
		$this->assertSame(
			array('session.test.' . $output->cookies['testSession']['value'] => array('test' => 'testValue')),
			$storedData, 'Stored data is incorrect');
	}

	/**
	 * Tests the error handling in the session
	 *
	 * @return void
	 */
	public function testErrorHandling() {
		parent::testErrorHandling();
		$storage = $this->getStorageMock();

		try {
			$this->getSession(null, array(), $storage, false, 'missingCookieName');
			$this->fail('No config exception thrown for missing required cookie name');
		} catch (ConfigException $exception) {
		}
	}
}