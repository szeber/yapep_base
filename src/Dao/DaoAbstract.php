<?php
declare(strict_types=1);

namespace YapepBase\Dao;

use YapepBase\Database\ConnectionHandler;
use YapepBase\DataBase\Exception\Exception;
use YapepBase\Database\Result;

/**
 * Common ancestor of Data Access Objects.
 */
abstract class DaoAbstract
{
    abstract protected function getConnection(): ConnectionHandler;

    /**
     * Runs a paginated query, and returns the result.
     *
     * You can't use LIMIT or OFFSET clause in your query, because then it will be duplicated in the method.
     *
     * Be warned! You have to write the SELECT keyword in uppercase in order to work properly.
     *
     * @throws Exception
     */
    protected function queryPaged(string $query, array $params, int $pageNumber, int $itemsPerPage, ?int &$itemCount = null): Result
    {
        if ($itemCount !== null) {
            $query = preg_replace('#SELECT#', '$0 SQL_CALC_FOUND_ROWS', $query, 1);
        }

        $offset = ($pageNumber - 1) * $itemsPerPage;
        $query .= ' LIMIT ' . $itemsPerPage . ' OFFSET ' . $offset;

        $result = $this->getConnection()->query($query, $params);

        if ($itemCount !== null) {
            $itemCount = (int)$this->getConnection()->query('SELECT FOUND_ROWS()')->fetchColumn();
        }

        return $result;
    }

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
