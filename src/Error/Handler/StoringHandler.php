<?php
declare(strict_types=1);

namespace YapepBase\Error\Handler;

use YapepBase\Error\Entity\Error;
use YapepBase\Error\Entity\ExceptionEntity;
use YapepBase\Error\Handler\Formatter\IFormatter;
use YapepBase\Request\IRequest;
use YapepBase\Storage\IStorage;

/**
 * Error handler that dumps debugging information to a specified storage.
 */
class StoringHandler implements IErrorHandler
{
    /** @varIStorage */
    private $storage;
    /** @var IFormatter */
    private $formatter;
    /** @var IRequest|null */
    private $request;

    public function __construct(IStorage $storage, IFormatter $formatter, ?IRequest $request = null)
    {
        $this->storage   = $storage;
        $this->formatter = $formatter;
        $this->request   = $request;
    }

    public function handleError(Error $error): void
    {
        $errorId = $error->getId();
        if ($this->isStoredAlready($errorId)) {
            return;
        }

        $debugData = $this->getDebugData($errorId, (string)$error, $error->getBacktrace());

        $this->storage->set($errorId, $debugData);
    }

    public function handleException(ExceptionEntity $exception): void
    {
        $errorId = $exception->getErrorId();
        if ($this->isStoredAlready($errorId)) {
            return;
        }

        $debugData = $this->getDebugData($errorId, (string)$exception, $exception->getException()->getTrace());

        $this->storage->set($errorId, $debugData);
    }

    public function handleShutdown(Error $error): void
    {
        $this->handleError($error);
    }

    private function getDebugData(string $errorId, string $errorMessage, array $backTrace = []): string
    {
        $result =  $errorId . ' ' . $errorMessage . PHP_EOL . PHP_EOL
            . $this->formatter->format('Debug backtrace', $backTrace);

        if (!empty($this->request)) {
            $result .= $this->formatter->format('Server', $this->request->getServer()->toArray())
                . $this->formatter->format('Get', $this->request->getQueryParams()->toArray())
                . $this->formatter->format('Post', $this->request->getPostParams()->toArray())
                . $this->formatter->format('Cookie', $this->request->getCookies()->toArray())
                . $this->formatter->format('Env', $this->request->getEnvParams()->toArray());
        }

        return $result;
    }

    private function isStoredAlready(string $errorId): bool
    {
        return !empty($this->storage->get($errorId));
    }
}
