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

use YapepBase\Database\DbConnection;
use YapepBase\Database\DbFactory;
use YapepBase\Exception\ParameterException;

/**
 * Table describe base class.
 *
 * Describes a table structure, and can execute simple queries on the table.
 *
 * @package    YapepBase
 * @subpackage Database
 */
abstract class DbTable {
	/** Constants for ascending order. */
	const ORDER_ASC = 'asc';
	/** Constants for descending order. */
	const ORDER_DESC = 'desc';

	/**
	 * The name of the table.
	 *
	 * @var string
	 */
	protected $tableName;

	/**
	 * The connection what should be used for the queries (if you want to override the default connection).
	 *
	 * @var DbConnection
	 */
	protected $dbConnection;

	/**
	 * The default connection name what should be used for the database connection.
	 *
	 * @var string
	 */
	protected $defaultDbConnectionName;

	/**
	 * Constructor
	 *
	 * @param DbConnection $dbConnection   The connection what should be used by the class.
	 *                                     If given, it will override the default connection.
	 *                                     Be cautious! The given connection will be used for both reading
	 *                                     and writing queries!
	 */
	public function __construct(DbConnection $dbConnection = null) {
		if ($dbConnection !== null) {
			$this->dbConnection = $dbConnection;
		}
	}

	/**
	 * Visszaadja az adatbazis tabla nevet, amelyen az objektum dolgozik.
	 *
	 * @return string
	 */
	public function getTableName() {
		return $this->tableName;
	}

	/**
	 * Returns the connection for the given type of query.
	 *
	 * @param int $type   The type of the query. Can be reading ({@link DbFactory::TYPE_READ_ONLY}),
	 *                    writing ({@DbFactory Db::TYPE_READ_WRITE}).
	 *
	 * @return DbConnection
	 */
	protected function getDbConnection($type) {
		if ($this->dbConnection !== null) {
			return $this->dbConnection;
		}
		else {
			return DbFactory::getConnection($this->defaultDbConnectionName, $type);
		}
	}

	/**
	 * Returns the fields of the described table.
	 *
	 * @return array   The fields of the table.
	 */
	abstract public function getFields();

	/**
	 * Returns the identifier of the query in a comment block.
	 *
	 * Useful for connecting a query with the code from a log.
	 *
	 * @param string $method   The name of the called method.
	 *
	 * @return string
	 */
	abstract protected function getQueryIdComment($method);

	/**
	 * Returns the given table or field name prepared for the query.
	 *
	 * @param string $name   The name of the table or field.
	 *
	 * @return string
	 */
	abstract protected function quoteEntity($name);

	/**
	 * Creates an INSERT query for one row.
	 *
	 * @param array $data         The data of the insertable row. The keys are the name of the fields.
	 * @param array $params       This will hold the params what can be passed to the query.(Outgoing Param)
	 * @param array $updateData   In case of integrity constraint violation, this array will be used to update the row.
	 *                            The keys are the name of the fields.
	 *
	 * @return string   The INSERT query.
	 */
	abstract protected function buildInsertQuery(array $data, array &$params, array $updateData = array());

	/**
	 * Generates the query needed to delete the deired records.
	 *
	 * @param array $data         The new data, the keys are the fields and the values are the values of the fields.
	 * @param array $conditions   The conditions, the keys are the fields and the values are the values of the fields.
	 * @param array $params       This will hold the params what can be passed to the query.(Outgoing Param)
	 *
	 * @return string   The generated SQL query.
	 */
	abstract protected function buildUpdateQuery(array $data, array $conditions, array &$params);

	/**
	 * Generates the query needed to delete the deired records.
	 *
	 * @param array $conditions   The conditions, the keys are the fields and the values are the values of the fields.
	 * @param array $params       This will hold the params what can be passed to the query.(Outgoing Param)
	 *
	 * @return string   The generated SQL query.
	 */
	abstract protected function buildDeleteQuery(array $conditions, array &$params);

	/**
	 * Generates the query needed to select the desired records.
	 *
	 * @param array  $conditions   The conditions, the keys are the fields and the values are the values of the fields.
	 * @param string $orderBy      The name of thee field, what should be used for ordering the result.
	 * @param string $direction    The direction of the order ({@link DbTable::ORDER_ASC}, {@link DbTable::ORDER_DESC}).
	 * @param array  $params       This will hold the params what can be passed to the query.(Outgoing Param)
	 * @param int    $limit        How may rows should be returned, if 0 or smaller number
	 *                             all of the records will be returned.
	 *
	 * @return string   The generated SQL query.
	 */
	abstract protected function buildSelectQuery(array $conditions, $orderBy, $direction, array &$params, $limit = 0);

	/**
	 * Inserts a record in to th table
	 *
	 * @param array $insertData      The record what should be inserted. The keys are the name of the fields,
	 *                               the values are the values of the fields.
	 * @param array $updateData      In case of integrity constraint violation, this array will be used to update the row.
	 * @param int   &$lastInsertId   Automaticly generated id of the inserted row(If there's any in the table).
	 *                               If TRUE, the value will be populated here (Outgoing parameter).
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\DatabaseException   On execution errors.
	 */
	public function insert(array $insertData, array $updateData = array(), &$lastInsertId = false) {
		$connection = $this->getDbConnection(DbFactory::TYPE_READ_WRITE);

		$params = array();
		$connection->query(
			$this->getQueryIdComment(__METHOD__) . "\n" .  $this->buildInsertQuery($insertData, $params, $updateData),
			$params
		);
		if ($lastInsertId !== false) {
			$lastInsertId = $connection->lastInsertId();
		}
	}

	/**
	 * Updates the rows what meets the conditions.
	 *
	 * @param array $data         The new data, the keys are the fields and the values are the values of the fields.
	 * @param array $conditions   The conditions, the keys are the fields and the values are the values of the fields.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\DatabaseException   On execution errors.
	 */
	public function update(array $data, array $conditions = array()) {
		$params = array();
		$this->getDbConnection(DbFactory::TYPE_READ_WRITE)->query(
			$this->getQueryIdComment(__METHOD__) . "\n" .  $this->buildUpdateQuery($data, $conditions, $params),
			$params
		);
	}

	/**
	 * Deletes the rows what meets the conditions.
	 *
	 * @param array $conditions   The conditions, the keys are the fields and the values are the values of the fields.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\DatabaseException   On execution errors.
	 */
	public function delete(array $conditions) {
		$params = array();
		$this->getDbConnection(DbFactory::TYPE_READ_WRITE)->query(
			$this->getQueryIdComment(__METHOD__) . "\n" .  $this->buildDeleteQuery($conditions, $params),
			$params
		);
	}

	/**
	 * Returns the rows what meets the given conditions. Only search by equality.
	 *
	 * @param array  $conditions   The conditions, the keys are the fields and the values are the values of the fields.
	 * @param string $orderBy      The name of thee field, what should be used for ordering the result.
	 * @param string $direction    The direction of the order ({@link DbTable::ORDER_ASC}, {@link DbTable::ORDER_DESC}).
	 * @param int    $limit        Maximum how many rows should be returned.
	 *
	 * @return array   An array containing the rows.
	 *
	 * @throws \YapepBase\Exception\ParameterException   If the given direction is unproper.
	 * @throws \YapepBase\Exception\DatabaseException    On execution errors.
	 */
	public function select(array $conditions, $orderBy = null, $direction = null, $limit = null) {
		if (!in_array($direction, array(self::ORDER_ASC, self::ORDER_DESC, null))) {
			throw new ParameterException('Unknown direction: ' . $direction);
		}

		$params = array();
		return $this->getDbConnection(
			DbFactory::TYPE_READ_ONLY)->query(
				$this->getQueryIdComment(__METHOD__) . "\n"
					.  $this->buildSelectQuery($conditions, $orderBy, $direction, $params, $limit),
				$params
		)->fetchAll();
	}

	/**
	 * Visszaadja a felteteleknek megfelelo rekordokat lapozhato modon lekerdezve. Csak egyenloseg alapjan keres.
	 *
	 * @param array  $conditions     The conditions, the keys are the fields and the values are the values of the fields.
	 * @param string $orderBy        The name of thee field, what should be used for ordering the result.
	 * @param string $direction      The direction of the order ({@link DbTable::ORDER_ASC}, {@link DbTable::ORDER_DESC}).
	 * @param int    $pageNumber     The number of the requested page (indexed from 1).
	 * @param int    $itemsPerPage   How many rows should be returned at a time.
	 * @param bool   &$itemCount     If its TRUE than the count of rows met the given conditions will be populated.
	 *                               (Outgoing parameter)Az osszes (eredmeny) elem szama lapozas nelkul.
	 *
	 * @return array   An array containing the rows.
	 *
	 * @throws \YapepBase\Exception\ParameterException   If no orderBy was given, or the given direction is unproper.
	 * @throws \YapepBase\Exception\DatabaseException    On execution errors.
	 */
	public function selectPaged(array $conditions, $orderBy, $direction, $pageNumber, $itemsPerPage,
		&$itemCount = false) {

		if (empty($orderBy)) {
			throw new ParameterException('It is not wise to paginate without an order!');
		}
		if (!in_array($direction, array(self::ORDER_ASC, self::ORDER_DESC))) {
			throw new ParameterException('Unknown direction: ' . $direction);
		}

		$params = array();
		return $this->getDbConnection(DbFactory::TYPE_READ_ONLY)->queryPaged(
			$this->getQueryIdComment(__METHOD__) . "\n"
				. $this->buildSelectQuery($conditions, $orderBy, $direction, $params),
			$params,
			$pageNumber, $itemsPerPage, $itemCount
		)->fetchAll();
	}

	/**
	 * Returns the first row what meets the given conditions. Only search by equality.
	 *
	 * @param array  $conditions   The conditions, the keys are the fields and the values are the values of the fields.
	 * @param string $orderBy      The name of thee field, what should be used for ordering the result.
	 * @param string $direction    The direction of the order ({@link DbTable::ORDER_ASC}, {@link DbTable::ORDER_DESC}).
	 *
	 * @return array   An associativ array represents a record in the table,
	 *                 or FALSE if there wasn't any row what met the conditions.
	 *
	 * @throws \YapepBase\Exception\ParameterException   If the given direction is unproper.
	 * @throws \YapepBase\Exception\DatabaseException   On execution errors.
	 */
	public function selectOne(array $conditions, $orderBy = null, $direction = null) {
		if (!in_array($direction, array(self::ORDER_ASC, self::ORDER_DESC, null))) {
			throw new ParameterException('Unknown direction: ' . $direction);
		}
		$params = array();

		return $this->getDbConnection(DbFactory::TYPE_READ_ONLY)->query(
			$this->getQueryIdComment(__METHOD__) . "\n"
				.  $this->buildSelectQuery($conditions, $orderBy, $direction, $params, 1),
			$params
		)->fetch();
	}
}
