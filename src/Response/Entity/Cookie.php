<?php
declare(strict_types=1);

namespace YapepBase\Response\Entity;

use YapepBase\Helper\DateHelper;

class Cookie
{
    /** @var string */
    protected $name;

    /** @var string */
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

    /** @var DateHelper */
    protected $dateHelper;

    public function __construct(
        string $name,
        string $value,
        int $ttlInSeconds = 0,
        string $path = '/',
        string $domain = '',
        bool $secure = false,
        bool $httpOnly = false,
        ?DateHelper $dateHelper = null
    ) {
        $this->name         = $name;
        $this->value        = $value;
        $this->ttlInSeconds = $ttlInSeconds;
        $this->path         = $path;
        $this->domain       = $domain;
        $this->secure       = $secure;
        $this->httpOnly     = $httpOnly;

        $this->dateHelper = empty($dateHelper)
            ? new DateHelper()
            : $dateHelper;
    }

    public function send(): bool
    {
        return setcookie(
            $this->name,
            $this->value,
            $this->getExpirationTime(),
            $this->path,
            $this->domain,
            $this->secure,
            $this->httpOnly
        );
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

    public function setSecure(bool $secure): self
    {
        $this->secure = $secure;

        return $this;
    }

    public function setHttpOnly(bool $httpOnly): self
    {
        $this->httpOnly = $httpOnly;

        return $this;
    }

    public function toHeader(): Header
    {
        $headerName       = 'Set-Cookie';
        $headerValueParts = [$this->name . '=' . $this->value];

        if (!empty($this->ttlInSeconds)) {
            $expirationTime     = date('D, d-M-Y H:i:s', $this->getExpirationTime()) . ' GMT; Max-Age=' . $this->ttlInSeconds;
            $headerValueParts[] = 'expires=' . $expirationTime;
        }

        $headerValueParts[] = 'path=' . $this->path;

        if (!empty($this->domain)) {
            $headerValueParts[] = 'domain=' . $this->domain;
        }

        if (!empty($this->secure)) {
            $headerValueParts[] = 'secure';
        }

        if (!empty($this->httpOnly)) {
            $headerValueParts[] = 'HttpOnly';
        }

        return new Header($headerName, implode('; ', $headerValueParts));
    }

    protected function getExpirationTime(): int
    {
        return empty($this->ttlInSeconds)
            ? 0
            : $this->dateHelper->getCurrentTimestamp() + $this->ttlInSeconds;
    }
}
