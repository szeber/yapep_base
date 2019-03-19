<?php
declare(strict_types=1);

namespace YapepBase\Debug\Item;

/**
 * Item which represents an Error triggered.
 */
class Error extends ItemAbstract
{
    /** @var int */
    protected $code;
    /** @var string */
    protected $message;
    /** @var string */
    protected $file;
    /** @var int */
    protected $line;
    /** @var array */
    protected $context = [];
    /** @var string */
    protected $id;

    public function __construct(int $code, string $message, string $file, int $line, array $context, string $id)
    {
        $this->code    = $code;
        $this->message = $message;
        $this->file    = $file;
        $this->line    = $line;
        $this->context = $context;
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

    public function getLine(): int
    {
        return $this->line;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function jsonSerialize()
    {
        return [
            'code'    => $this->code,
            'message' => $this->message,
            'file'    => $this->file,
            'line'    => $this->line,
            'context' => $this->context,
            'id'      => $this->id,
        ];
    }
}
