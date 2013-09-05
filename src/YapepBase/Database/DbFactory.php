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
use YapepBase\Exception\ConfigException;
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
 * <b>resource.database.&lt;connectionName&gt;.&lt;connectionType&gt;.&lt;optionName&gt;</b>
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
			$properties = array(
				'backendType',
				'charset',
				'database',
				'host',
				'password',
				'path',
				'user',
			);
			$configData = array();
			foreach ($properties as $property) {
				try {
					$configData[$property] =
						$config->get('resource.database.' . $connectionName . '.' . $connectionType . '.' . $property);

				}
				catch (ConfigException $e) {
					// We just swallow this because we don't know what properties do we need in advance
				}
			}

			if (empty($configData)) {
				throw new DatabaseException('Database connection configuration "' . $connectionName . '" not found');
			}

			if (!static::validateConnectionConfig($configData)) {
				throw new DatabaseException('Invalid database config: ' . $connectionName);
			}

			static::$connections[$connectionName][$connectionType]
				= static::makeConnection($configData, $connectionName);
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
	 * @return bool   TRUE if the given configuration is valid, FALSE otherwise.
	 */
	protected static function validateConnectionConfig(array $configuration) {
		if (!isset($configuration['backendType'])) {
			return false;
		}

		switch ($configuration['backendType']) {
			case self::BACKEND_TYPE_MYSQL:
				// We need all these information to connect to a MySQL server
				if (
					empty($configuration['host'])
					|| empty($configuration['user'])
					|| is_null($configuration['password'])
					|| empty($configuration['charset'])
					|| empty($configuration['database'])
				) {
					return false;
				}
				break;

			case self::BACKEND_TYPE_SQLITE:
				// We need only the path to connect to an SQLite
				if (empty($configuration['path'])) {
					return false;
				}
				break;

			// We have an unknown backend type
			default:
				return false;
		}

		return true;
	}

	/**
	 * Creates a database connection instance/
	 *
	 * @param array  $configuration    The configuration data.
	 * @param string $connectionName   The name of the connection.
	 *
	 * @return DbConnection   The connection instance.
	 *
	 * @throws DatabaseException   On configuration or connection errors.
	 */
	protected static function makeConnection(array $configuration, $connectionName) {
		switch ($configuration['backendType']) {
			case self::BACKEND_TYPE_MYSQL:
				return new MysqlConnection($configuration, $connectionName, static::$paramPrefix);
				break;

			case self::BACKEND_TYPE_SQLITE:
				return new SqliteConnection($configuration, $connectionName, static::$paramPrefix);
				break;

			default:
				throw new DatabaseException('Invalid database config: ' . $connectionName);
		}
	}

	/**
	 * Clearing all the stored connections from the class.
	 *
	 * @return void
	 */
	public static function clearConnections() {
		foreach (self::$connections as $connectionTypes) {
			foreach($connectionTypes as $connection) {
				/** @var \YapepBase\Database\DbConnection $connection */
				$connection->disconnect();
			}
		}
		self::$connections = array();
	}
}