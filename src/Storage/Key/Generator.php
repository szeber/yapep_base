<?php
declare(strict_types=1);

namespace YapepBase\Storage\Key;

class Generator implements IGenerator
{
    /** @var bool */
    protected $hashing;
    /** @var string */
    protected $prefix;
    /** @var string */
    protected $suffix;

    public function __construct(bool $hashing, string $prefix = '', string $suffix = '')
    {
        $this->hashing = $hashing;
        $this->prefix  = $prefix;
        $this->suffix  = $suffix;
    }

    public function generate(string $key): string
    {
        $key = $this->prefix . $key . $this->suffix;
        if ($this->hashing) {
            $key = md5($key);
        }

        return $key;
    }

    public function isHashing(): bool
    {
        return $this->hashing;
    }

    public function setHashing(bool $hash)
    {
        $this->hashing = $hash;

        return $this;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getSuffix(): string
    {
        return $this->suffix;
    }

    public function setSuffix(string $suffix)
    {
        $this->suffix = $suffix;

        return $this;
    }
}
