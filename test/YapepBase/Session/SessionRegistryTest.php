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

namespace YapepBase\Session;
use YapepBase\Exception\Exception;
use YapepBase\Session\SessionRegistry;
use YapepBase\Mock\Session\SessionMock;

/**
 * SessionRegistryTest class
 *
 * @package    YapepBase
 * @subpackage Test\Session
 */
class SessionRegistryTest extends \PHPUnit_Framework_TestCase {

	public function testRegistration() {
		$session = new SessionMock('test');
		$registry = new SessionRegistry();

		$registry->register($session);
		$this->assertSame($session, $registry->getSession('test'));
	}

	public function testErrorHandling() {
		$registry = new SessionRegistry();
		try {
			$registry->getSession('test');
			$this->fail('No error thrown for nonexistent session request');
		} catch (Exception $exception) {}

	}

}

