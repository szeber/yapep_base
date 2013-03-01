<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Session
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Session;
use YapepBase\Storage\IStorage;

/**
 * Simple session handler.
 *
 * This session handler takes a session ID when instantiated.
 *
 * @package    YapepBase
 * @subpackage Session
 */
class SimpleSession extends SessionAbstract {

	/**
	 * The session ID.
	 *
	 * @var string
	 */
	protected $sessionId;

	/**
	 * Constructor
	 *
	 * @param string                        $configName     Name of the session config.
	 * @param \YapepBase\Storage\IStorage   $storage        The storage object.
	 * @param string                        $sessionId      ID of the session.
	 * @param bool                          $autoRegister   If TRUE, it will automatically register as an event handler.
	 */
	public function __construct($configName, IStorage $storage, $sessionId, $autoRegister = true) {
		$this->sessionId = $sessionId;
		parent::__construct($configName, $storage, $autoRegister);
	}

	/**
	 * Validates the configuration.
	 *
	 * @param array $config   The configuration array.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\ConfigException   On configuration problems
	 * @throws \YapepBase\Exception\Exception         On other problems
	 */
	protected function validateConfig(array $config) {
		// No configuration is necessary.
	}

	/**
	 * Returns the session ID. If there is no session ID, it returns NULL.
	 *
	 * @return string
	 */
	protected function getSessionId() {
		return $this->sessionId;
	}

}