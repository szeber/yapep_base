<?php
declare(strict_types=1);

namespace YapepBase\Error\Entity;

use YapepBase\Error\Helper\ErrorHelper;

class ExceptionEntity
{
    /** @var \Throwable */
    private $exception;

    /** @var string|null */
    private $errorId;

    public function __construct(\Throwable $exception, ?string $errorId = null)
    {
        $this->errorId   = $errorId;
        $this->exception = $exception;
    }

    public function getException(): \Throwable
    {
        return $this->exception;
    }

    public function getErrorId(): string
    {
        return $this->errorId;
    }

    public function setErrorId(?string $errorId): self
    {
        $this->errorId = $errorId;

        return $this;
    }

    public function toError(): Error
    {
        return new Error(ErrorHelper::E_EXCEPTION, $this->exception->getMessage(), $this->exception->getFile(),
            $this->exception->getLine(), $this->errorId);
    }

    public function __toString(): string
    {
        return '[' . ErrorHelper::E_EXCEPTION_DESCRIPTION . ']: Unhandled ' . get_class($this->exception) . ': '
            . $this->exception->getMessage() . '(' . $this->exception->getCode()
            . ') on line ' . $this->exception->getLine() . ' in '
            . $this->exception->getFile();
    }
}
