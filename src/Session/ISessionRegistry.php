<?php
declare(strict_types = 1);

namespace YapepBase\Session;

use YapepBase\Exception\Exception;

/**
 * Registry containing all registered sessions.
 */
interface ISessionRegistry
{
    /**
     * Returns the session corresponding to namespace
     *
     * @throws Exception   If no session is registered with the specified namespace.
     */
    public function getSession(string $namespace): ISession;

    /**
     * Registers a session
     */
    public function register(ISession $session): void;

    /**
     * Returns all of the stored data from the sessions grouped by the namespaces.
     *
     * @return array   An associative array where the keys are the names of the namespace,
     *                 and the values are the stored data from the session.
     */
    public function getAllData(): array;
}
