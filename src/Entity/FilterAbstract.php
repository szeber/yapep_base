<?php
declare(strict_types=1);

namespace YapepBase\Entity;

use YapepBase\Exception\InvalidArgumentException;

class FilterAbstract
{
    /** @var int|null */
    private $page;
    /** @var int|null */
    private $itemsPerPage;
    /** @var string|null */
    private $orderField;
    /** @var int|null */
    private $orderDirection;

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
    public function setOrderField(string $orderField)
    {
        $this->orderField = $orderField;

        return $this;
    }

    /**
     * @return static
     */
    public function setOrderDirectionAscending()
    {
        $this->orderDirection = 1;
        return $this;
    }

    /**
     * @return static
     */
    public function setOrderDirectionDescending()
    {
        $this->orderDirection = -1;
        return $this;
    }

    public function getOrderField(): ?string
    {
        return $this->orderField;
    }

    public function getOrderDirection(): ?int
    {
        return $this->orderDirection;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function getItemsPerPage(): ?int
    {
        return $this->itemsPerPage;
    }

    public function getId(): string
    {
        $properties = (string)print_r($this, true);

        return md5($properties);
    }
}
