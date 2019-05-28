<?php
declare(strict_types=1);

namespace YapepBase\Entity;

use YapepBase\Exception\InvalidArgumentException;

/**
 * Represents a filter object.
 */
abstract class FilterAbstract
{
    /** @var int|null */
    protected $page;
    /** @var int|null */
    protected $itemsPerPage;
    /** @var string|null */
    protected $orderField;
    /** @var bool */
    protected $isOrderReversed = false;

    /**
     * @return static
     *
     * @throws InvalidArgumentException
     */
    public function setPage(int $page)
    {
        if ($page < 1) {
            throw new InvalidArgumentException();
        }

        $this->page = $page;

        return $this;
    }

    /**
     * @return static
     *
     * @throws InvalidArgumentException
     */
    public function setItemsPerPage(int $itemsPerPage)
    {
        if ($itemsPerPage < 1) {
            throw new InvalidArgumentException();
        }

        $this->itemsPerPage = $itemsPerPage;

        return $this;
    }

    /**
     * @return static
     */
    public function setOrderField(string $order)
    {
        $this->orderField = $order;

        return $this;
    }

    /**
     * @return static
     */
    public function setReverseOrder()
    {
        $this->isOrderReversed = true;
        return $this;
    }

    public function getOrderField(): ?string
    {
        return $this->orderField;
    }

    public function isOrderReversed(): bool
    {
        return $this->isOrderReversed;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function getItemsPerPage(): ?int
    {
        return $this->itemsPerPage;
    }
}
