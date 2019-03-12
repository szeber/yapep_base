<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Database;

use PDO;

use YapepBase\Exception\DatabaseException;

/**
 * MySQL database connection implementation.
 */
class MysqlConnection extends DbConnection
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
        $dsn = 'mysql:host=' . $configuration['host'] . ';dbname=' . $configuration['database'];
        if (!empty($configuration['port'])) {
            $dsn .= ';port=' . $configuration['port'];
        }
        $options = (
            (!empty($configuration['options']) && is_array($configuration['options']))
            ? $configuration['options']
            : []
        );

        if (isset($configuration['isPersistent'])) {
            $options[PDO::ATTR_PERSISTENT] = (bool)$configuration['isPersistent'];
        }

        $this->connection = new PDO($dsn, $configuration['user'], $configuration['password'], $options);

        $this->query('SET NAMES ' . (empty($configuration['charset'])
            ? 'utf8'
            : $configuration['charset']));

        if (isset($configuration['useTraditionalStrictMode']) && $configuration['useTraditionalStrictMode']) {
            $this->query('SET @@SESSION.sql_mode = \'TRADITIONAL\'; ');
        }

        if (isset($configuration['timezone'])) {
            $this->query('SET time_zone=:' . $this->paramPrefix . 'tz', ['tz' => $configuration['timezone']]);
        }
    }

    /**
     * Returns the backend type for the given connection {@uses DbFactory::BACKEND_TYPE_*}
     *
     * @return string
     */
    protected function getBackendType()
    {
        return DbFactory::BACKEND_TYPE_MYSQL;
    }

    /**
     * Runs a query and returns the result object.
     *
     * @param string $query    The query to execute.
     * @param array  $params   The parameters for the query.
     *
     * @return \YapepBase\Database\DbResult   The result of the query.
     *
     * @throws \YapepBase\Exception\DatabaseException   On execution errors.
     */
    public function query($query, array $params = [])
    {
        // Try to run it in a loop so we can retry on certain errors
        for ($i = 0; $i < 2; $i++) {
            try {
                return parent::query($query, $params);
            } catch (DatabaseException $e) {
                // The mySQL server has gone away, we try to run the query again, but only once
                if ($e->getMessage() == 'SQLSTATE[HY000]: General error: 2006 MySQL server has gone away' && $i == 0) {
                    $this->connect($this->configuration);
                } else {
                    throw $e;
                }
            }
        }
    }
}
