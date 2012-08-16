<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Test\Mock\Session
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Mock\Session;
use YapepBase\Event\Event;
use YapepBase\Session\ISession;

/**
 * SessionMock class
 *
 * @package    YapepBase
 * @subpackage Test\Mock\Session
 * @codeCoverageIgnore
 */
class SessionMock implements ISession {

	protected $namespace;

	public function __construct($namespace) {
		$this->namespace = $namespace;
	}

	/**
	 * (non-PHPdoc)
	 * @see YapepBase\Session.ISession::create()
	 */
	public function create() {
	}

	/**
	 * (non-PHPdoc)
	 * @see YapepBase\Session.ISession::destroy()
	 */
	public function destroy() {
	}

	/**
	 * (non-PHPdoc)
	 * @see YapepBase\Session.ISession::getNamespace()
	 */
	public function getNamespace() {
		return $this->namespace;
	}

	/**
	 * (non-PHPdoc)
	 * @see YapepBase\Session.ISession::registerEventHandler()
	 */
	public function registerEventHandler() {
	}

	/**
	 * (non-PHPdoc)
	 * @see YapepBase\Session.ISession::removeEventHandler()
	 */
	public function removeEventHandler() {
	}

	/**
	 * (non-PHPdoc)
	 * @see YapepBase\Event.IEventHandler::handleEvent()
	 */
	public function handleEvent(Event $event) {
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($offset) {
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($offset) {
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetSet()
	 */
	public function offsetSet($offset, $value) {
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset($offset) {
	}

	/**
	 * (non-PHPdoc)
	 * @see ArrayAccess::offsetUnset()
	 */
	public function getId() {
	}

	/**
	 * Returns only the data from the session.
	 *
	 * @return array
	 */
	public function getData() {
	}
}