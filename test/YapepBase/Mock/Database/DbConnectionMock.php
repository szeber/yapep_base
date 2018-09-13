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
	}

	protected function getBackendType() {
	}

	public function getParamType(&$value) {
		return parent::getParamType($value);
	}

}
