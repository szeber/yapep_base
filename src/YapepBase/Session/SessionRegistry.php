<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Session
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Session;
use YapepBase\Exception\Exception;
use YapepBase\Session\SessionAbstract;

/**
 * Registry containig all registered sessions.
 *
 * @package    YapepBase
 * @subpackage Session
 */
class SessionRegistry {

	/**
	 * Stores the registered namespaces
	 *
	 * @var array
	 */
	protected $namespaces = array();

	/**
	 * Returns the session coresponding to namespace
	 *
	 * @param string $namespace   The namespace.
	 *
	 * @return \YapepBase\Session\ISession   The session object.
	 *
	 * @throws \YapepBase\Exception\Exception   If no session is registered with the specified namespace.
	 */
	public function getSession($namespace) {
		if (!isset($this->namespaces[$namespace])) {
			throw new Exception('Namespace not registered: ' . $namespace);
		}
		return $this->namespaces[$namespace];
	}

	/**
	 * Registers a session
	 *
	 * @param \YapepBase\Session\ISession $session   The session object
	 *
	 * @return void
	 */
	public function register(ISession $session) {
		$this->namespaces[$session->getNamespace()] = $session;
	}

	/**
	 * Returns all of the stored data from the sessions grouped by the namespaces.
	 *
	 * @return array   An associative array where the keys are the names of the namespace,
	 *                 and the values are the stored data from the session.
	 */
	public function getAllData() {
		$result = array();

		/** @var \YapepBase\Session\ISession $data   The stored session. */
		foreach ($this->namespaces as $namespace => $data) {
			$result[$namespace] = $data->getData();
		}
		return $result;
	}
}