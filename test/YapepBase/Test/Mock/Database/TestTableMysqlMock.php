<?php

namespace YapepBase\Test\Mock\Database;

/**
 * Mock class for PDOStatement
 *
 * @codeCoverageIgnore
 */
class TestTableMysqlMock extends \YapepBase\Database\MysqlTable {
	/**
	 * The name of the table.
	 *
	 * @var string
	 */
	protected $tableName = 'test';

	/**
	 * The default connection name what should be used for the database connection.
	 *
	 * @var string
	 */
	protected $defaultDbConnectionName = 'test';

	/** The id field. */
	const FIELD_ID = 'id';
	/** The key field. */
	const FIELD_KEY = 'key';
	/** The value field. */
	const FIELD_VALUE = 'value';

	/**
	 * Returns the fields of the table.
	 *
	 * @return array
	 */
	public function getFields() {
		return array(
			self::FIELD_ID,
			self::FIELD_KEY,
			self::FIELD_VALUE
		);
	}
}