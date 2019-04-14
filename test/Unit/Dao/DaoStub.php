<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Dao;

use YapepBase\Dao\DaoAbstract;
use YapepBase\Database\ConnectionHandler;

class DaoStub extends DaoAbstract
{
    /** @var ConnectionHandler */
    protected $connection;

    protected function getConnection(): ConnectionHandler
    {
        return $this->connection;
    }

    public function setConnection(ConnectionHandler $connection)
    {
        $this->connection = $connection;
    }

    public function setEqualityCondition(
        array &$conditions,
        array &$queryParams,
        string $fieldName,
        $expectation,
        string $tableAlias = '',
        bool $onlyNullConsideredEmpty = false
    ) {
        parent::setEqualityCondition($conditions, $queryParams, $fieldName, $expectation, $tableAlias, $onlyNullConsideredEmpty);
    }

    public function setInListCondition(
        array &$conditions,
        array &$queryParams,
        string $fieldName,
        array $values,
        string $tableAlias = '',
        bool $isNegated = false
    ) {
        parent::setInListCondition($conditions, $queryParams, $fieldName, $values, $tableAlias, $isNegated);
    }

    public function getPrefixedField(string $tableAlias, string $fieldName): string
    {
        return parent::getPrefixedField($tableAlias, $fieldName);
    }

    public function getParamName(string $fieldName, string $tableAlias = '', ?int $index = null): string
    {
        return parent::getParamName($fieldName, $tableAlias, $index);
    }
}
