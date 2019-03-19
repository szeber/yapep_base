<?php
declare(strict_types=1);

namespace YapepBase\Debug\Item;

use YapepBase\Helper\DateHelper;

/**
 * Item which represents a triggered event in the applications lifecycle.
 */
class Event extends ItemAbstract
{
    /** @var string */
    protected $name;
    /** @var array */
    protected $data = [];
    /** @var float */
    protected $triggeredAt;

    public function __construct(DateHelper $dateHelper, string $name, array $data = [])
    {
        parent::__construct($dateHelper);

        $this->name        = $name;
        $this->data        = $data;
        $this->triggeredAt = $this->getDateHelper()->getCurrentTimestampUs();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getTriggeredAt(): float
    {
        return $this->triggeredAt;
    }

    public function jsonSerialize()
    {
        return [
            'name'        => $this->name,
            'data'        => $this->data,
            'triggeredAt' => $this->triggeredAt,
        ];
    }
}
