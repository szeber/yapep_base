<?php
declare(strict_types=1);

namespace YapepBase\Debug\Item;

use YapepBase\Helper\DateHelper;

trait THasExecutionTime
{
    abstract protected function getDateHelper(): DateHelper;

    /** @var float */
    protected $startTime;
    /** @var float|null */
    protected $finishTime;

    protected function setStartTime()
    {
        $this->startTime = $this->getDateHelper()->getCurrentTimestampUs();
    }

    public function setFinished(): self
    {
        $this->finishTime = $this->getDateHelper()->getCurrentTimestampUs();

        return $this;
    }

    public function getStartTime(): float
    {
        return $this->startTime;
    }

    public function getFinishTime(): ?float
    {
        return $this->finishTime;
    }

    public function getExecutionTime(): ?float
    {
        if (empty($this->finishTime)) {
            return null;
        }

        return $this->finishTime - $this->startTime;
    }

    protected function getDateForJsonSerialize(): array
    {
        return [
            'startTime'  => $this->startTime,
            'finishTime' => $this->finishTime,
        ];
    }
}
