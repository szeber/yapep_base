<?php
declare(strict_types=1);

namespace YapepBase\Error\Entity;

use YapepBase\Error\Helper\ErrorHelper;

class Error
{
    /** @var int */
    private $code;
    /** @var string  */
    private $message;
    /** @var string */
    private $file;
    /** @var $line */
    private $line;
    /** @var string|null */
    private $id;
    /** @var array */
    private $backtrace = [];

    public function __construct(int $code, string $message, string $file, $line, ?string $id = null)
    {
        $this->code    = $code;
        $this->message = $message;
        $this->file    = $file;
        $this->line    = $line;
        $this->id      = $id;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getLine()
    {
        return $this->line;
    }

    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getBacktrace(): array
    {
        return $this->backtrace;
    }

    public function setBacktrace(array $backtrace): self
    {
        $this->backtrace = $backtrace;

        return $this;
    }

    public function __toString(): string
    {
        $errorDescription = (new ErrorHelper())->getDescription($this->getCode());

        return '[' . $errorDescription . '(' . $this->getCode() . ')]: '
            . $this->getMessage()
            . ' on line ' . $this->getLine()
            . ' in ' . $this->getFile();
    }
}
