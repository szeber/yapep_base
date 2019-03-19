<?php
declare(strict_types=1);

namespace YapepBase\Debug\Item;

use YapepBase\Helper\DateHelper;

/**
 * A general item what can used to store anything what's not handled by the framework
 */
class General extends ItemAbstract
{
    use THasExecutionTime;

    /** @var string */
    protected $name;
    /** @var array */
    protected $data = [];

    public function __construct(DateHelper $dateHelper, string $name, array $data = [])
    {
        parent::__construct($dateHelper);

        $this->name = $name;
        $this->data = $data;
        $this->setStartTime();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function jsonSerialize()
    {
        return array_merge(
            [
                'name' => $this->name,
                'data' => $this->data,
            ],
            $this->getDateForJsonSerialize()
        );
    }
}
