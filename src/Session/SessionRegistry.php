<?php
declare(strict_types=1);

namespace YapepBase\Session;

use YapepBase\Exception\Exception;

/**
 * Registry containing all registered sessions.
 */
class SessionRegistry implements ISessionRegistry
{
    /**
     * @var array
     */
    protected $namespaces = [];

    public function getSession(string $namespace): ISession
    {
        if (!isset($this->namespaces[$namespace])) {
            throw new Exception('Namespace not registered: ' . $namespace);
        }

        return $this->namespaces[$namespace];
    }

    public function register(ISession $session): void
    {
        $this->namespaces[$session->getNamespace()] = $session;
    }

    public function getAllData(): array
    {
        $result = [];

        /** @var ISession $data */
        foreach ($this->namespaces as $namespace => $data) {
            $result[$namespace] = $data->getData();
        }
        return $result;
    }
}
