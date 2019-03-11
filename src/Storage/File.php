<?php
declare(strict_types=1);

namespace YapepBase\Storage;

class File
{
    /** @var mixed */
    public $data;
    /** @var int|null */
    public $createdAt;
    /** @var int|null */
    public $expiresAt;

    public function __construct($data, int $createdAt = null, int $expiresAt = null)
    {
        $this->data      = $data;
        $this->createdAt = $createdAt;
        $this->expiresAt = $expiresAt;
    }
}
