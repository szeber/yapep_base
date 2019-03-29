<?php
declare(strict_types=1);

namespace YapepBase\Response\Entity;

use YapepBase\Exception\InvalidArgumentException;

class HeaderContainer
{
    /** @var string */
    protected $name;
    /** @var Header[] */
    protected $headersByValue = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function add(Header $header): self
    {
        if ($header->getName() !== $this->name) {
            throw new InvalidArgumentException('Name of container should be the same as the added headers');
        }

        $this->headersByValue[$header->getValue()] = $header;

        return $this;
    }

    public function remove(Header $header): self
    {
        unset($this->headersByValue[$header->getValue()]);

        return $this;
    }

    public function clear(): self
    {
        $this->headersByValue = [];

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Header[]
     */
    public function toArray(): array
    {
        return $this->headersByValue;
    }
}
