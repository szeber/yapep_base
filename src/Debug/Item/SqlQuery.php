<?php
declare(strict_types = 1);

namespace YapepBase\Debug\Item;

use YapepBase\Helper\DateHelper;

/**
 * Item which stores the details of an SQL query being executed.
 */
class SqlQuery extends ItemAbstract
{
    use THasExecutionTime;

    /** @var string */
    protected $dsn;
    /** @var string */
    protected $query;
    /** @var array */
    protected $params = [];
    /** @var string|null */
    protected $callerClass;
    /** @var string|null */
    protected $callerMethod;

    public function __construct(
        DateHelper $dateHelper,
        string $dsn,
        string $query,
        array $params,
        ?string $callerClass = null,
        ?string $callerMethod = null
    ) {
        parent::__construct($dateHelper);

        $this->dsn          = $dsn;
        $this->query        = $query;
        $this->params       = $params;
        $this->callerClass  = $callerClass;
        $this->callerMethod = $callerMethod;

        $this->setStartTime();
    }

    public function getDsn(): string
    {
        return $this->dsn;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getCallerClass(): ?string
    {
        return $this->callerClass;
    }

    public function getCallerMethod(): ?string
    {
        return $this->callerMethod;
    }

    public function jsonSerialize()
    {
        return array_merge(
            [
                'dsn'          => $this->dsn,
                'query'        => $this->query,
                'params'       => $this->params,
                'callerClass'  => $this->callerClass,
                'callerMethod' => $this->callerMethod,
            ],
            $this->getDateForJsonSerialize()
        );
    }
}
