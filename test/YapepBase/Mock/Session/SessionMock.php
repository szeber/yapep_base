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

	/**
	 * The current namespace.
	 *
	 * @var string
	 */
	protected $namespace;

	/**
	 * The data stored in the session.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Constructor.
	 *
	 * @param string $namespace   The namespace of the session.
	 */
	public function __construct($namespace) {
		$this->namespace = $namespace;
	}

	/**
	 * Creates the session.
	 *
	 * @return void
	 */
	public function create() {
		$this->data = array();
	}

	/**
	 * Destroys the session.
	 *
	 * @return void
	 */
	public function destroy() {
		$this->data = array();
	}

	/**
	 * Returns the current namespace.
	 *
	 * @return string
	 */
	public function getNamespace() {
		return $this->namespace;
	}

	/**
	 * Registers the instance as an event handler
	 *
	 * @return void
	 */
	public function registerEventHandler() {
	}

	/**
	 * Removes event handler registration
	 *
	 * @return void
	 */
	public function removeEventHandler() {
	}

	/**
	 * Handles an event
	 *
	 * @param \YapepBase\Event\Event $event   The dispatched event.
	 *
	 * @return void
	 */
	public function handleEvent(Event $event) {
	}

	/**
	 * Checks whether a key is set in the session
	 *
	 * @param string $offset   The offset.
	 *
	 * @return bool
	 *
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($offset) {
		return isset($this->data[$offset]);

	}

	/**
	 * Returns a key from the session
	 *
	 * @param string $offset   The offset to return.
	 *
	 * @return mixed
	 *
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($offset) {
		if (isset($this->data[$offset])) {
			return $this->data[$offset];
		}
		return null;
	}

	/**
	 * Sets a key in the session
	 *
	 * @param string $offset   The offset.
	 * @param mixed  $value    The value.
	 *
	 * @return void
	 *
	 * @see ArrayAccess::offsetSet()
	 */
	public function offsetSet($offset, $value) {
		$this->data[$offset] = $value;

	}

	/**
	 * Removes a key from the session
	 *
	 * @param string $offset   The offset.
	 *
	 * @return void
	 *
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset($offset) {
		if (isset($this->data[$offset])) {
			unset($this->data[$offset]);
		}
	}

	/**
	 * Returns the sessionId.
	 *
	 * @return int
	 */
	public function getId() {
	}

	/**
	 * Returns only the data from the session.
	 *
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * Loads the session.
	 *
	 * If there is no session ID set, it creates a new session instead.
	 *
	 * @return void
	 */
	public function loadSession() {
	}

	/**
	 * Saves the session.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\Exception   If trying to save a not loaded session
	 */
	public function saveSession() {
	}

}