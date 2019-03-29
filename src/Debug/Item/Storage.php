<?php
declare(strict_types=1);

namespace YapepBase\Debug\Item;

use YapepBase\Exception\InvalidArgumentException;
use YapepBase\Helper\DateHelper;

/**
 * Item which represents a storage usage.
 */
class Storage extends ItemAbstract
{
    use THasExecutionTime;

    /** Set storage method. */
    const METHOD_SET = 'set';
    /** Get storage method. */
    const METHOD_GET = 'get';
    /** Delete storage method. */
    const METHOD_DELETE = 'delete';
    /** Clear storage method. */
    const METHOD_CLEAR = 'clear';
    /** Increment storage method. */
    const METHOD_INCREMENT = 'increment';

    /** @var string */
    protected $method;
    /** @var string|null */
    protected $key;
    /** @var mixed */
    protected $data;

    public function __construct(DateHelper $dateHelper, string $method, ?string $key = null, $data = null)
    {
        parent::__construct($dateHelper);

        $this->setMethod($method);

        $this->key  = $key;
        $this->data = $data;

        $this->setStartTime();
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getStartTime(): float
    {
        return $this->startTime;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function setMethod(string $method)
    {
        $availableMethods = [
            self::METHOD_SET,
            self::METHOD_GET,
            self::METHOD_DELETE,
            self::METHOD_CLEAR,
            self::METHOD_INCREMENT,
        ];
        if (!in_array($method, $availableMethods)) {
            throw new InvalidArgumentException('Invalid method given: ' . $method);
        }

        $this->method = $method;
    }

    public function jsonSerialize()
    {
        return array_merge(
            [
                'method' => $this->method,
                'key'    => $this->key,
                'data'   => $this->data,
            ],
            $this->getDateForJsonSerialize()
        );
    }
}
