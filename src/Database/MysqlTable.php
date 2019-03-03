<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Database
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Database;


use YapepBase\Database\DbFactory;

/**
 * Table describe base class for MySQL tables.
 *
 * Describes a table structure, and can execute simple queries on the table.
 *
 * @package    YapepBase
 * @subpackage Database
 */
abstract class MysqlTable extends DbTable {

	/**
	 * Returns the identifier of the query in a comment block.
	 *
	 * Useful for connecting a query with the code from a log.
	 *
	 * @param string $method   The name of the called method.
	 *
	 * @return string
	 */
	protected function getQueryIdComment($method) {
		$method = str_replace(__CLASS__ . '::', '', $method);
		return '/** QID: ' . get_class($this) . '::' . $method . ' */';
	}

	/**
	 * Returns the given table or field name prepared for the query.
	 *
	 * @param string $name   The name of the table or field.
	 *
	 * @return string
	 */
	protected function quoteEntity($name) {
		return '`' . $name . '`';
	}

	/**
	 * Creates a param list what can be used in prepared statements for inserting rows.
	 *
	 * @param array $data        The data what will be used in the query (Can be a record or a collection of records).
	 * @param array $paramList   The list of the params(what can be concatenated to the query) will be populated here
	 *                            (outgoing param).
	 *
	 * @return array   An associative array where the keys are
	 */
	protected function buildInsertParamListForPreparedStatement(array $data, array &$paramList) {
		// We're providing the keys with a prefix in order to avoid key duplications
		$keyPrefix = 'insert';
		$result = array();

		foreach ($data as $key => $value) {
			// If the value is an array then miltiple rows will be inserted
			if (is_array($value)) {
				foreach ($value as $innerKey => $innerValue) {
					$paramName = $keyPrefix . '_' . $innerKey . '_' . $key;
					$paramList[$key][] = ':'
						. $this->getDbConnection(DbFactory::TYPE_READ_ONLY)->getParamPrefix()
						. $paramName;
					$result[$paramName] = $innerValue;
				}
			}
			// There will be only one row inserted
			else {
				$paramName = $keyPrefix . '_' . $key;
				$paramList[] = ':'
					. $this->getDbConnection(DbFactory::TYPE_READ_ONLY)->getParamPrefix()
					. $paramName;
				$result[$paramName] = $value;
			}
		}

		return $result;
	}

	/**
	 * Creates a param list what can be used in prepared statements for updating rows.
	 *
	 * @param array $data        The data what will be used in the query.
	 * @param array $paramList   The list of the params(what can be concatenated to the query) will be populated here
	 *                            (outgoing param).
	 *
	 * @return array   An associative array where the keys are
	 */
	protected function buildUpdateParamListForPreparedStatement(array $data, array &$paramList) {
		// We're providing the keys with a prefix in order to avoid key duplications
		$keyPrefix = 'update';
		$result = array();

		foreach ($data as $key => $value) {
			$paramName = $keyPrefix . '_' . $key;
			$paramList[] = $this->quoteEntity($key)
				. ' = :'
				. $this->getDbConnection(DbFactory::TYPE_READ_ONLY)->getParamPrefix()
				. $paramName;
			$result[$paramName] = $value;
		}

		return $result;
	}

	/**
	 * Creates a param list what can be used in the WHERE block of a prepared statement.
	 *
	 * @param array $data        The data what will be used in the query.
	 * @param array $paramList   The list of the params(what can be concatenated to the query) will be populated here
	 *                            (outgoing param).
	 *
	 * @return array   An associative array where the keys are
	 */
	protected function buildConditionParamListForPreparedStatement(array $data, array &$paramList) {
		// We're providing the keys with a prefix in order to avoid key duplications
		$keyPrefix = 'condition';
		$result = array();

		foreach ($data as $key => $value) {
			// The value is an array so it is a list of possible values
			if (is_array($value)) {
				$possibleParamNames = array();
				foreach ($value as $index => $possibleValue) {
					$paramName = $keyPrefix . '_' . $key . '_' . $index;

					$result[$paramName] = $possibleValue;
					$possibleParamNames[] = ':' . $this->getDbConnection(DbFactory::TYPE_READ_ONLY)->getParamPrefix()
						. $paramName;
				}
				$paramList[] = $this->quoteEntity($key) . ' IN (' . implode(', ', $possibleParamNames) . ')';

			} else {
				$paramName = $keyPrefix . '_' . $key;
				// If the value is null we have to use the IS NULL expression
				if ($value === null) {
					$operator = 'IS';
				} else {
					$operator = '=';
				}
				$paramList[] = $this->quoteEntity($key)
					. ' ' . $operator . ' '
					. ':'
					. $this->getDbConnection(DbFactory::TYPE_READ_ONLY)->getParamPrefix()
					. $paramName;

				$result[$paramName] = $value;
			}
		}

		return $result;
	}

	/**
	 * Creates an INSERT query.
	 *
	 * @param array $data         The data of the insertable row or rows. (Can be a record or a collection of records)
	 * @param array $params       This will hold the params what can be passed to the query.(Outgoing Param)
	 * @param array $updateData   In case of integrity constraint violation, this array will be used to update the row.
	 *                            The keys are the name of the fields.
	 *
	 * @return string   The INSERT query.
	 */
	protected function buildInsertQuery(array $data, array &$params, array $updateData = array()) {
		$paramListInsert = array();
		$params = $this->buildInsertParamListForPreparedStatement($data, $paramListInsert);

		// If there will be multiple rows inserted, then we have to search for the field names one level deeper
		$fieldList = array();
		foreach ($data as $value) {
			if (is_array($value)) {
				$fieldList = array_keys($value);
			}
			else {
				$fieldList = array_keys($data);
			}
			break;
		}

		$insert = '
			INSERT INTO
				' . $this->quoteEntity($this->tableName) . '
				(' . implode(', ', array_map(array($this, 'quoteEntity'), $fieldList)) . ')
			VALUES
		';

		$rows = array();

		// If its an array we have to create multiple rows
		foreach ($paramListInsert as $value) {
			if (is_array($value)) {
				$rows[] = '(' . implode(', ', $value) . ')';
			}
		}
		// If there are no rows, it means that there will be only one row inserted
		if (empty($rows)) {
			$rows[] = '(' . implode(', ', $paramListInsert) . ')';
		}
		$insert .= implode(', ', $rows);

		// We have an ON DUPLICATE KEY UPDATE clause
		if (!empty($updateData)) {
			$paramListUpdate = array();
			$params += $this->buildUpdateParamListForPreparedStatement($updateData, $paramListUpdate);

			$insert .= '
				ON DUPLICATE KEY UPDATE
					' . implode(', ', $paramListUpdate);
		}

		return $insert;
	}

	/**
	 * Generates the query needed to delete the desired records.
	 *
	 * @param array $data         The new data, the keys are the fields and the values are the values of the fields.
	 * @param array $conditions   The conditions, the keys are the fields and the values are the values of the fields.
	 * @param array $params       This will hold the params what can be passed to the query.(Outgoing Param)
	 *
	 * @return string   The generated SQL query.
	 */
	protected function buildUpdateQuery(array $data, array $conditions, array &$params) {
		$paramListUpdate = array();
		$paramListCondition = array();
		$params = $this->buildUpdateParamListForPreparedStatement($data, $paramListUpdate);
		$params += $this->buildConditionParamListForPreparedStatement($conditions, $paramListCondition);

		return '
			UPDATE
				' . $this->quoteEntity($this->tableName) . '
			SET
				' . implode(', ', $paramListUpdate) . '
			WHERE
				' . (empty($conditions)
			? 'TRUE'
			: implode(' AND ', $paramListCondition)
		)
			;
	}

	/**
	 * Generates the query needed to delete the desired records.
	 *
	 * @param array $conditions   The conditions, the keys are the fields and the values are the values of the fields.
	 * @param array $params       This will hold the params what can be passed to the query.(Outgoing Param)
	 *
	 * @return string   The generated SQL query.
	 */
	protected function buildDeleteQuery(array $conditions, array &$params) {
		$paramList = array();
		$params = $this->buildConditionParamListForPreparedStatement($conditions, $paramList);

		return '
			DELETE FROM
				' . $this->quoteEntity($this->tableName) . '
			WHERE
				' . (empty($conditions)
			? 'TRUE'
			: implode(' AND ', $paramList)
		)
			;
	}

	/**
	 * Generates the query needed to select the desired records.
	 *
	 * @param array  $conditions   The conditions, the keys are the fields and the values are the values of the fields.
	 * @param string $orderBy      The name of the field, what should be used for ordering the result.
	 * @param string $direction    The direction of the order ({@link DbTable::ORDER_ASC}, {@link DbTable::ORDER_DESC}).
	 * @param array  $params       This will hold the params what can be passed to the query.(Outgoing Param)
	 * @param int    $limit        How may rows should be returned, if 0 or smaller number
	 *                             all of the records will be returned.
	 *
	 * @return string   The generated SQL query.
	 */
	protected function buildSelectQuery(array $conditions, $orderBy, $direction, array &$params, $limit = 0) {
		$paramList = array();
		$params = $this->buildConditionParamListForPreparedStatement($conditions, $paramList);

		$query = '
			SELECT
				*
			FROM
				' . $this->quoteEntity($this->tableName) . '
			WHERE
				' . (empty($conditions)
			? 'TRUE'
			: implode(' AND ', $paramList)
		)
		;

		if (!empty($orderBy)) {
			$query .= '
				ORDER BY
					' . $this->quoteEntity($orderBy) . ' ';

			switch ($direction) {
				case self::ORDER_ASC:
					$query .= 'ASC';
					break;

				case self::ORDER_DESC:
					$query .= 'DESC';
					break;
			}
		}

		$query .= $limit <= 0 ? '' : '
			LIMIT
				' . $limit
		;

		return $query;
	}
}