<?php

namespace YapepBase\Mock\Database;


use YapepBase\Database\DbConnection;

/**
 * Mock class for MysqlConnection
 *
 * @codeCoverageIgnore
 */
class DbConnectionMock extends DbConnection {

	public function __construct() {
	}

	protected function connect(array $configuration) {
		// TODO: Implement connect() method.
	}

	protected function getBackendType() {
		// TODO: Implement getBackendType() method.
	}

	public function getParamType(&$value) {
		return parent::getParamType($value);
	}

}
