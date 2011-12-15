<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Test\Session
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Test\Session;
use YapepBase\Application;
use YapepBase\Session\SessionRegistry;
use YapepBase\Exception\ConfigException;
use YapepBase\Exception\Exception;
use YapepBase\Test\Mock\Response\ResponseMock;
use YapepBase\Event\Event;
use YapepBase\Test\Mock\Response\OutputMock;
use YapepBase\Response\HttpResponse;
use YapepBase\Request\HttpRequest;
use YapepBase\Test\Mock\Request\RequestMock;
use YapepBase\Test\Mock\Storage\StorageMock;
use YapepBase\Config;
use YapepBase\Session\HttpSession;

/**
 * HttpSessionTest class
 *
 * @package    YapepBase
 * @subpackage Test\Session
 *
 * @todo refactor the huge testFunctionality method
 */
class HttpSessionTest extends \PHPUnit_Framework_TestCase {

    public function setUp() {
        parent::setUp();
        Config::getInstance()->set(array(
            'test.session.namespace' => 'test',
            'test.session.cookieName' => 'testSession',
            'test.session.cookieDomain' => 'testdomain',
            'test.session.cookiePath' => '/test',
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

    public function testFunctionality() {
        $storage = $this->getStorageMock();
        $request = new HttpRequest(array(), array(), array(), array('REQUEST_URI' => '/'), array(), array());
        $output = new OutputMock();
        $response = new HttpResponse($output);
		$session = new HttpSession('test.session.*', $storage, $request, $response, false);

        $session->handleEvent(new Event(Event::TYPE_APPSTART));

        $sessionId = $session->getId();

        $this->assertSame('test', $session->getNamespace(), 'The session namespace does not match');

        $session['test'] = 'testValue';
        $this->assertTrue(isset($session['test']), 'isset() for existing key fails');
        $this->assertFalse(isset($session['test2']), 'isset() for non-existing key fails');
        $this->assertSame('testValue', $session['test'], 'Readback from session does not match');
        $this->assertNull($session['test2'], 'Get for non-existing key does not return NULL');

        $session->handleEvent(new Event(Event::TYPE_APPSTART));
        $this->assertSame('testValue', $session['test'], 'Second start event call overwrites the session data');

        $session->handleEvent(new Event(Event::TYPE_APPFINISH));

        $storedData = $storage->getData();
        $this->assertEquals(1, count($storedData), 'The store should have 1 item');
        $this->assertTrue(isset($storedData['session.test.' . $sessionId]), 'Stored session ID does not match');
        $this->assertSame(array('test' => 'testValue'), $storedData['session.test.' . $sessionId],
        	'Stored session data does not match');

        $existingRequest = new HttpRequest(array(), array(), array('testSession' => $sessionId),
            array('REQUEST_URI' => '/'), array(), array());

        $response->send();

        $this->assertTrue(isset($output->cookies['testSession']), 'Session cookie is not set');
        $this->assertEquals($sessionId, $output->cookies['testSession']['value'],
        	'Session cookie is not set to the correct value');

        $this->assertTrue(
            isset($output->headers['Expires'])
                && isset($output->headers['Cache-Control'])
                && isset($output->headers['Pragma']),
            'Cache limiters are not sent'
        );

        $output = new OutputMock();
        $response = new HttpResponse($output);

        $session = new HttpSession('test.session.*', $storage, $existingRequest, $response);

        $session->handleEvent(new Event(Event::TYPE_APPSTART));

        $this->assertSame($sessionId, $session->getId(), 'Session ID does not match for existing session');

        $this->assertSame('testValue', $session['test'], 'Readback fails for existing session');

        unset($session['test']);

        $this->assertNull($session['test'], 'Key deletion failed');

        $session->handleEvent(new Event(Event::TYPE_APPFINISH));

        $storedData = $storage->getData();
        $this->assertEquals(1, count($storedData), 'The store should have 1 item');
        $this->assertTrue(isset($storedData['session.test.' . $sessionId]), 'Stored session ID does not match');
        $this->assertTrue(empty($storedData['session.test.' . $sessionId]), 'Stored data was not emptied');

        $output = new OutputMock();
        $response = new HttpResponse($output);

        $session = new HttpSession('test.session.*', $storage, $existingRequest, $response);

        $session->handleEvent(new Event(Event::TYPE_APPSTART));

        $session->create();
        $newSessionId = $session->getId();

        $this->assertNotEquals($sessionId, $newSessionId, 'Creating a new session did not change the sessionId');

        $session->handleEvent(new Event(Event::TYPE_APPFINISH));

        $response->send();

        $storedData = $storage->getData();
        $this->assertEquals(1, count($storedData), 'The store should have 1 item');
        $this->assertFalse(isset($storedData['session.test.' . $sessionId]),
        	'The destroyed session data was not deleted');
        $this->assertTrue(isset($storedData['session.test.' . $newSessionId]),
        	'The newly created session data was not saved');
    }

    public function testInvalidSessionHandling() {
        $storage = $this->getStorageMock();
        $request = new HttpRequest(array(), array(), array('testSession' => 'nonexistent'),
            array('REQUEST_URI' => '/'), array(), array());
        $output = new OutputMock();
        $response = new HttpResponse($output);

		$session = new HttpSession('test.session.*', $storage, $request, $response, false);

		$session->handleEvent(new Event(Event::TYPE_APPSTART));

		$this->assertNotEquals('nonexistent', $session->getId(), 'The session ID was not changed');
    }

    public function testEventHandling() {
        $output = new OutputMock();
        $storage = $this->getStorageMock();
        $request = new HttpRequest(array(), array(), array(), array('REQUEST_URI' => '/'), array(), array());
		$response = new HttpResponse($output);
        $eventHandlerRegistry = Application::getInstance()->getDiContainer()->getEventHandlerRegistry();
		$session = new HttpSession('test.session.*', $storage, $request, $response, true);

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
		    new HttpSession('test.session.*', $nonTtlStorage, $request, $response);
            $this->fail('Non TTL supporting storage does not cause an exception');
		} catch(Exception $e) {}

		try {
		    new HttpSession('test.nonexistingSession', $storage, $request, $response);
		    $this->fail('Non existing config does not cause an exception');
		} catch (ConfigException $exception) {}

		try {
	        new HttpSession('test.session.namespace', $storage, $request, $response);
	        $this->fail('Non array config option does not cause an exception');
		} catch (ConfigException $exception) {}

        Config::getInstance()->set(array(
            'test.session2.namespace' => 'test',
        ));
        try {
            new HttpSession('test.session2.*', $storage, $request, $response);
            $this->fail('No config exception thrown for missing required cookie name');
        } catch (ConfigException $exception) {}

        Config::getInstance()->set(array(
            'test.session3.cookieName' => 'testSession',
        ));
        try {
            new HttpSession('test.session3.*', $storage, $request, $response);
            $this->fail('No config exception thrown for missing required namespace');
        } catch (ConfigException $exception) {}

        try {
            new HttpSession('test.session.*', $storage, $requestMock, $response);
            $this->fail('No exception thrown for non HttpRequest request instance');
        } catch (Exception $exception) {}


        try {
            new HttpSession('test.session.*', $storage, $request, $responseMock);
            $this->fail('No exception thrown for non HttpResponse response instance');
        } catch (Exception $exception) {}

		try {
		    $session = new HttpSession('test.session.*', $storage, $request, $response);
		    $session->handleEvent(new Event(Event::TYPE_APPFINISH));
            $this->fail('No exception thrown when trying to save a not yet loaded session');
		} catch(Exception $e) {}

    }

}