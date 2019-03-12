<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Dao;

use YapepBase\Database\DbConnection;

/**
 * Base abstract DAO which should be extended by every DAO class.
 */
abstract class DaoAbstract
{
    /** Interval unit for day. */
    const INTERVAL_UNIT_DAY = 'day';
    /** Interval unit for month. */
    const INTERVAL_UNIT_MONTH = 'month';
    /** Interval unit for year. */
    const INTERVAL_UNIT_YEAR = 'year';

    /**
     * Generates a condition like [tableAlias].[fieldName] = [expectedValue]
     *
     * @param DbConnection $dbConnection              DB Connection.
     * @param string       $fieldName                 The field to check.
     * @param mixed        $expectation               What value should be the field matched against.
     * @param array        $conditions                Array which holds the conditions.
     * @param array        $queryParams               Array which holds the query params.
     * @param string       $tableAlias                Alias of the table which contains the given field.
     * @param bool         $onlyNullConsideredEmpty   If TRUE only null value will be considered empty
     */
    protected function getEqualityCondition(
        DbConnection $dbConnection,
        $fieldName,
        $expectation,
        array &$conditions,
        array &$queryParams,
        $tableAlias = '',
        $onlyNullConsideredEmpty = false
    ) {
        if (
            (!$onlyNullConsideredEmpty && empty($expectation))
            ||
            ($onlyNullConsideredEmpty && is_null($expectation))
        ) {
            return;
        }

        if (is_bool($expectation)) {
            $expectation = (int)$expectation;
        }

        $paramName = $this->getParamName($fieldName, $tableAlias);
        $fieldName = $this->getPrefixedField($tableAlias, $fieldName);

        $conditions[]            = $fieldName . ' = :' . $dbConnection->getParamPrefix() . $paramName;
        $queryParams[$paramName] = $expectation;
    }

    /**
     * Generates a condition like [tableAlias].[fieldName] IN ([list])
     *
     * @param DbConnection $dbConnection   DB Connection.
     * @param string       $fieldName      The field to check.
     * @param array        $list           List of the possible values
     * @param array        $conditions     Array which holds the conditions.
     * @param array        $queryParams    Array which holds the query params.
     * @param string       $tableAlias     Alias of the table which contains the given field.
     * @param bool         $isNegated      If TRUE, it will generate a NOT IN, with the default false it will generate an IN condition
     */
    protected function getInListCondition(
        DbConnection $dbConnection,
        $fieldName,
        array $list,
        array &$conditions,
        array &$queryParams,
        $tableAlias = '',
        $isNegated = false
    ) {
        if (empty($list)) {
            return;
        }

        $paramPrefix = $dbConnection->getParamPrefix();
        $paramNames  = [];
        foreach ($list as $index => $item) {
            $paramName = $this->getParamName($fieldName, $tableAlias, $index);

            $paramNames[]            = ':' . $paramPrefix . $paramName;
            $queryParams[$paramName] = $item;
        }

        $operator = $isNegated ? 'NOT IN' : 'IN';

        $conditions[] = $this->getPrefixedField($tableAlias, $fieldName) . ' ' . $operator . ' (' . implode(', ', $paramNames) . ')';
    }

    /**
     * Returns the name of the given field with the proper prefix.
     *
     * @param string $tableAlias   Alias of the table.
     * @param string $fieldName    Name of the field.
     *
     * @return string
     */
    protected function getPrefixedField($tableAlias, $fieldName)
    {
        $prefix = empty($tableAlias) ? '' : $tableAlias . '.';

        return $prefix . $fieldName;
    }

    /**
     * Creates a parameter name based on the given field and table.
     *
     * @param string   $fieldName    Name of the field.
     * @param string   $tableAlias   Alias of the table
     * @param int|null $index        An index number if the same field and table is used more than once.
     *
     * @return string
     */
    protected function getParamName($fieldName, $tableAlias = '', $index = null)
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
