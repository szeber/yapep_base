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

/**
 * SimpleSessionTest class
 *
 * @package    YapepBase
 * @subpackage Test\Session
 */
class SimpleSessionTest extends SessionTestAbstract {

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
		$storage = empty($storage) ? $this->getStorageMock(true, $sessionData) : $storage;
		return new SimpleSession($configName, $storage, $sessionId, $autoRegister);
	}
}