<?php
declare(strict_types=1);

namespace YapepBase\Response\Entity;

class Cookie
{
    /** @var string */
    protected $name;

    /** @var string  */
    protected $value;

    /** @var int */
    protected $ttlInSeconds;

    /** @var string */
    protected $path;

    /** @var string */
    protected $domain;

    /** @var bool */
    protected $secure;

    /** @var bool */
    protected $httpOnly;

    public function __construct(
        string $name,
        string $value,
        int $ttlInSeconds = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = false
    ) {
        $this->name         = $name;
        $this->value        = $value;
        $this->ttlInSeconds = $ttlInSeconds;
        $this->path         = $path;
        $this->domain       = $domain;
        $this->secure       = $secure;
        $this->httpOnly     = $httpOnly;
    }

    public function send(): bool
    {
        return setcookie($this->name, $this->value, $this->ttlInSeconds, $this->path,
            $this->domain, $this->secure, $this->httpOnly);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getTtlInSeconds(): int
    {
        return $this->ttlInSeconds;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function isSecure(): bool
    {
        return $this->secure;
    }

    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }
}
