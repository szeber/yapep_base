<?php

namespace YapepBase\Mock\Database;


use YapepBase\Database\MysqlConnection;

/**
 * Mock class for MysqlConnection
 *
 * @codeCoverageIgnore
 */
class MysqlConnectionMock extends MysqlConnection {

	/**
	 * Constructor.
	 *
	 * @param PdoMock $pdo   PDO connection to use
	 */
	public function __construct(PdoMock $pdo) {
		$this->connection = $pdo;
	}

	/**
	 * Opens the connection.
	 *
	 * @param array $configuration   The configuration for the connection.
	 *
	 * @return void
	 *
	 * @throws \PDOException   On connection errors.
	 */
	protected function connect(array $configuration) {
	}
}