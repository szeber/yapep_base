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
use YapepBase\Event\IEventHandler;

/**
 * Session interface
 *
 * @package    YapepBase
 * @subpackage Session
 */
interface ISession extends \ArrayAccess, IEventHandler {
	/**
	 * Registers the instance as an event handler
	 */
	public function registerEventHandler();

	/**
	 * Removes event handler registration
	 */
	public function removeEventHandler();

	/**
	 * Creates a new session.
	 */
	public function create();

	/**
	 * Destroys the session.
	 */
	public function destroy();

	/**
	 * Returns the session's namespace
	 *
	 * @return string
	 */
	public function getNamespace();

	/**
	 * Returns the session ID
	 *
	 * @return string
	 */
	public function getId();

}