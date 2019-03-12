<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Session;

use YapepBase\Event\IEventHandler;

/**
 * Session interface
 */
interface ISession extends \ArrayAccess, IEventHandler
{
    /**
     * Registers the instance as an event handler
     *
     * @return void
     */
    public function registerEventHandler();

    /**
     * Removes event handler registration
     *
     * @return void
     */
    public function removeEventHandler();

    /**
     * Creates a new session.
     *
     * @return void
     */
    public function create();

    /**
     * Destroys the session.
     *
     * @return void
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

    /**
     * Returns only the data from the session.
     *
     * @return array
     */
    public function getData();

    /**
     * Loads the session.
     *
     * If there is no session ID set, it creates a new session instead.
     *
     * @return void
     */
    public function loadSession();

    /**
     * Saves the session.
     *
     * @return void
     *
     * @throws \YapepBase\Exception\Exception   If trying to save a not loaded session
     */
    public function saveSession();
}
