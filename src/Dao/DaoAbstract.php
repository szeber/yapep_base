<?php
declare(strict_types=1);

namespace YapepBase\Dao;

use YapepBase\Database\DbConnection;

/**
 * Common ancestor of Data Access Objects.
 */
abstract class DaoAbstract
{
    abstract protected function getConnection(): DbConnection;

    /**
     * Generates a condition like [tableAlias].[fieldName] = [expectedValue]
     */
    protected function setEqualityCondition(
        array &$conditions,
        array &$queryParams,
        string $fieldName,
        $expectation,
        string $tableAlias = '',
        bool $onlyNullConsideredEmpty = false
    ) {
        if ((!$onlyNullConsideredEmpty && empty($expectation)) || ($onlyNullConsideredEmpty && is_null($expectation))) {
            return;
        }

        if (is_bool($expectation)) {
            $expectation = (int)$expectation;
        }

        $paramName = $this->getParamName($fieldName, $tableAlias);
        $fieldName = $this->getPrefixedField($tableAlias, $fieldName);

        $conditions[]            = $fieldName . ' = :' . $this->getConnection()->getParamPrefix() . $paramName;
        $queryParams[$paramName] = $expectation;
    }

    /**
     * Generates a condition like [tableAlias].[fieldName] IN ([values])
     */
    protected function setInListCondition(
        array &$conditions,
        array &$queryParams,
        string $fieldName,
        array $values,
        string $tableAlias = '',
        bool $isNegated = false
    ) {
        if (empty($values)) {
            return;
        }

        $paramPrefix = $this->getConnection()->getParamPrefix();
        $paramNames  = [];
        foreach ($values as $index => $item) {
            $paramName = $this->getParamName($fieldName, $tableAlias, $index);

            $paramNames[]            = ':' . $paramPrefix . $paramName;
            $queryParams[$paramName] = $item;
        }

        $operator = $isNegated ? 'NOT IN' : 'IN';

        $conditions[] = $this->getPrefixedField($tableAlias, $fieldName)
            . ' ' . $operator . ' (' . implode(', ', $paramNames) . ')';
    }

    /**
     * Returns the name of the given field with the proper prefix.
     */
    protected function getPrefixedField(string $tableAlias, string $fieldName): string
    {
        $prefix = empty($tableAlias) ? '' : $tableAlias . '.';

        return $prefix . $fieldName;
    }

    /**
     * Creates a parameter name based on the given field and table.
     */
    protected function getParamName(string $fieldName, string $tableAlias = '', ?int $index = null): string
    {
        $paramNameParts = [];

        if (!empty($tableAlias)) {
            $paramNameParts[] = $tableAlias;
        }
        $paramNameParts[] = $fieldName;

        if (!is_null($index)) {
            $paramNameParts[] = $index;
        }

        return implode('_', $paramNameParts);
    }
}
