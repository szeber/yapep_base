<?php
declare(strict_types=1);

namespace YapepBase\File\Exception;

class NotFoundException extends Exception
{
    /**
     * @var string
     */
    protected $path;

    public function __construct(string $path, string $message)
    {
        parent::__construct($message);

        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
