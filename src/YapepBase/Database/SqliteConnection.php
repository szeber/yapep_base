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
use \PDO;

/**
 * Sqlite database connection implementation.
 *
 * @package    YapepBase
 * @subpackage Database
 */
class SqliteConnection extends DbConnection {

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
		$dsn = 'sqlite:' . $configuration['path'];
		$options = ((!empty($configuration['options']) && is_array($configuration['options']))
			? $configuration['options'] : array());

		$this->connection = new PDO($dsn, null, null, $options);
	}


}