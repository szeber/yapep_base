<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Dao;

use YapepBase\Dao\DaoAbstract;
use YapepBase\Database\DbConnection;

class DaoStub extends DaoAbstract
{
    /** @var DbConnection */
    protected $connection;

    protected function getConnection(): DbConnection
    {
        return $this->connection;
    }

    public function setConnection(DbConnection $connection)
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
