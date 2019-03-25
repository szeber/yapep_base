<?php
declare(strict_types=1);

namespace YapepBase\Response\Entity;

class Header
{
    /** @var string */
    protected $name;
    /** @var string|null */
    protected $value;

    public function __construct(string $name, ?string $value = null)
    {
        $this->name  = $name;
        $this->value = $value;
    }

    public function send(): void
    {
        header((string)$this, false);
    }

    public function __toString(): string
    {
        return $this->name  . (empty($this->value) ? '' : ': ' . $this->value);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }
}
