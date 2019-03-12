<?php
declare(strict_types = 1);

namespace YapepBase\Debug\Item;

/**
 * Item which stores the memory usage of the script at the instantiations moment.
 */
class MemoryUsage extends ItemAbstract
{
    /** @var string */
    protected $name;
    /** @var int */
    protected $current;
    /** @var int */
    protected $peak;

    public function __construct(string $name)
    {
        $this->name    = $name;
        $this->current = memory_get_usage(true);
        $this->peak    = memory_get_peak_usage(true);
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getCurrent(): int
    {
        return $this->current;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getPeak(): int
    {
        return $this->peak;
    }

    public function jsonSerialize()
    {
        return [
            'name'    => $this->name,
            'current' => $this->current,
            'peak'    => $this->peak,
        ];
    }
}
