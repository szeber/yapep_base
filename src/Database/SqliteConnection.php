<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Database;

use PDO;

/**
 * Sqlite database connection implementation.
 */
class SqliteConnection extends DbConnection
{
    /**
     * Opens the connection.
     *
     * @param array $configuration   The configuration for the connection.
     *
     * @return void
     *
     * @throws \PDOException   On connection errors.
     */
    protected function connect(array $configuration)
    {
        $dsn     = 'sqlite:' . $configuration['path'];
        $options = ((!empty($configuration['options']) && is_array($configuration['options']))
            ? $configuration['options'] : []);

        $this->connection = new PDO($dsn, null, null, $options);
    }

    /**
     * Returns the backend type for the given conneciton {@uses DbFactory::BACKEND_TYPE_*}
     *
     * @return string
     */
    protected function getBackendType()
    {
        return DbFactory::BACKEND_TYPE_SQLITE;
    }
}
