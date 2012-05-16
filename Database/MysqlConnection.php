<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Database
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Database;
use \PDO;

/**
 * MySQL database connection implementation.
 *
 * @package    YapepBase
 * @subpackage Database
 */
class MysqlConnection extends DbConnection {

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
		$dsn = 'mysql:host=' . $configuration['host'] . ';dbname=' . $configuration['database'];
		if (!empty($configuration['port'])) {
			$dsn .= ';port=' . $configuration['port'];
		}
		$options = ((!empty($configuration['options']) && is_array($configuration['options']))
			? $configuration['options']
			: array()
		);

		if (!isset($options[PDO::MYSQL_ATTR_INIT_COMMAND])) {
			$options[PDO::MYSQL_ATTR_INIT_COMMAND] = '';
		} elseif (';' != substr(trim($options[PDO::MYSQL_ATTR_INIT_COMMAND]), -1, 1)) {
			$options[PDO::MYSQL_ATTR_INIT_COMMAND] .= ';';
		}

		$options[PDO::MYSQL_ATTR_INIT_COMMAND] .= 'SET NAMES ' . (
			empty($configuration['charset'])
				? 'utf8'
				: $configuration['charset']
		) . ';';

		$this->connection = new PDO($dsn, $configuration['user'], $configuration['password'], $options);

		if (isset($configuration['useTraditionalStrictMode']) && $configuration['useTraditionalStrictMode']) {
			$this->query('SET @@SESSION.sql_mode = \'TRADITIONAL\'; ');
		}

		if (isset($configuration['timezone'])) {
			$this->query('SET time_zone=:_tz', array('tz' => $configuration['timezone']));
		}
	}
}