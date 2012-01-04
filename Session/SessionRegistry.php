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
 * SessionContainer class
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
     * @param string $namespace
     *
     * @return \YapepBase\Session\SessionAbstract   The session object.
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
     * @param SessionAbstract $session   The session object
     */
    public function register(ISession $session) {
        $this->namespaces[$session->getNamespace()] = $session;
    }
}