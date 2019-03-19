<?php
declare(strict_types=1);

namespace YapepBase\Debug\Item;

use YapepBase\Helper\DateHelper;

/**
 * A simple item which stores the moment of the instantiation with a given name
 */
class Time extends ItemAbstract
{
    /** @var string */
    protected $name;
    /** @var float */
    protected $instantiatedAt;

    public function __construct(DateHelper $dateHelper, string $name)
    {
        parent::__construct($dateHelper);

        $this->name           = $name;
        $this->instantiatedAt = $this->getDateHelper()->getCurrentTimestampUs();
    }

    public function getTimeElapsedSinceInitiated(float $dataHandlerInitiatedAt): float
    {
        return $this->instantiatedAt - $dataHandlerInitiatedAt;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getInstantiatedAt(): float
    {
        return $this->instantiatedAt;
    }

    public function jsonSerialize()
    {
        return [
            'name'           => $this->name,
            'instantiatedAt' => $this->instantiatedAt,
        ];
    }
}
