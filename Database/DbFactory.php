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

use YapepBase\Database\DbConnection;
use YapepBase\Exception\DatabaseException;
use YapepBase\Config;

/**
 * Factory class for database connections.
 *
 * Global database settings are set in the format:
 * system.database.<optionName>
 *
 * Global database options:
 *     <ul>
 *         <li>paramPrefix: The bound parameter prefix.</li>
 *     </ul>
 *
 * Configuration settings for the connections should be set in the format:
 * application.database.<connectionName>.<connectionType>.<optionName>
 *
 * Generic configuration:
 *     <ul>
 *         <li>backendType: The database backend type. {@uses self::BACKEND_TYPE_*}</li>
 *         <li>options: Associative array with options for the connection.</li>
 *     </ul>
 *
 * MySQL configuration:
 *     <ul>
 *         <li>host: The database host.</li>
 *         <li>user: The username.</li>
 *         <li>password: The password.</li>
 *         <li>database: The database name.</li>
 *         <li>charset: The character set of the connection.</li>
 *         <li>isPersistent: If TRUE, the connection will be persistent. Optional, defaults to FALSE.</li>
 *         <li>useTraditionalStrictMode: If TRUE, the connection will use TRADITIONAL sql_mode for each connection.
 *                                       Only needed if the server does not set it. Care should be taken about enabling
 *                                       this option, because it may break a lot of queries, that degrade gracefully
 *                                       without this option.
 *                                       {@see http://dev.mysql.com/doc/refman/5.0/en/server-sql-mode.html}</li>
 *	       <li>timezone: The timezone to set for the connection. Must already be escaped; may not be supported by
 *                       database server. Optional.</li>
 *     </ul>
 *
 * SQLite configuration
 *     <ul>
 *         <li>path: The full path to the database, or ':memory:' for memory databases.
 *     </ul>
 *
 * @package    YapepBase
 * @subpackage Database
 */
class DbFactory {
	/** Read only connection type. */
	const TYPE_READ_ONLY = 'ro';
	/** Read-write connection type. */
	const TYPE_READ_WRITE = 'rw';

	/** MySQL backend type */
	const BACKEND_TYPE_MYSQL = 'mysql';
	/** SQLite backend type. */
	const BACKEND_TYPE_SQLITE = 'sqlite';

	/**
	 * Stores the database connections.
	 *
	 * @var array
	 */
	protected static $connections = array();

	/**
	 * The parameter prefix for the bound parameters
	 *
	 * @var string
	 */
	protected static $paramPrefix;

	/**
	 * Returns a database connection of the specified name and type
	 *
	 * @param string $connectionName   The name of the database connection.
	 * @param string $connectionType   The type of the database connection. {@uses self::TYPE_*}
	 *
	 * @return DbConnection   The requested connection.
	 *
	 * @throws DatabaseException   On configuration or connection errors.
	 */
	public static function getConnection($connectionName, $connectionType = self::TYPE_READ_WRITE) {
		if (!isset(static::$connections[$connectionName][$connectionType])) {
			$config = Config::getInstance();
			if (is_null(static::$paramPrefix)) {
				static::$paramPrefix = (string)$config->get('system.database.paramPrefix', '');
			}
			$data = $config->get('application.database.' . $connectionName . '.' . $connectionType . '.*', false);
			if (empty($data) || !is_array($data)) {
				throw new DatabaseException('Database connection configuration not found');
			}
			static::validateConnectionConfig($data);
			static::$connections[$connectionName][$connectionType] = static::makeConnection($data, $connectionName, $connectionType);
			if (self::TYPE_READ_WRITE == $connectionType
				|| isset(static::$connections[$connectionName][self::TYPE_READ_ONLY])
			) {
				static::$connections[$connectionName][self::TYPE_READ_ONLY] =
					static::$connections[$connectionName][$connectionType];
			}
		}
		return static::$connections[$connectionName][$connectionType];
	}

	/**
	 * Validates the database connection config.
	 *
	 * @param array $configuration   The config to validate.
	 *
	 * @throws DatabaseException   On configuration errors.
	 */
	protected static function validateConnectionConfig(array $configuration) {
		if (!isset($configuration['backendType'])) {
			throw new DatabaseException('Invalid database config');
		}
		switch ($configuration['backendType']) {
			case self::BACKEND_TYPE_MYSQL:
				if (
					empty($configuration['host'])
					|| empty($configuration['user'])
					|| !isset($configuration['password'])
					|| empty($configuration['charset'])
					|| empty($configuration['database'])
				) {
					throw new DatabaseException('Invalid database config');
				}
				break;

			case self::BACKEND_TYPE_SQLITE:
				if (empty($configuration['path'])) {
					throw new DatabaseException('Invalid database config');
				}
				break;

			default:
				throw new DatabaseException('Invalid database config');
		}
	}

	/**
	 * Creates a database connection instance/
	 *
	 * @param array $configuration     The configuration data.
	 * @param string $connectionName   The name of the connection.
	 * @param string $connectionType   The type of the connection. {@uses self::TYPE_*}
	 *
	 * @return DbConnection   The connection instance.
	 *
	 * @throws DatabaseException   On configuration or connection errors.
	 */
	protected static function makeConnection(array $configuration, $connectionName, $connectionType) {
		switch ($configuration['backendType']) {
			case self::BACKEND_TYPE_MYSQL:
				return new MysqlConnection($configuration, $connectionName, static::$paramPrefix);
				break;

			case self::BACKEND_TYPE_SQLITE:
				return new SqliteConnection($configuration, $connectionName, static::$paramPrefix);
				break;

			default:
				throw new DatabaseException('Invalid database config');
		}
	}
}