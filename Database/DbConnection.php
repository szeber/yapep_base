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
use YapepBase\Exception\DatabaseException;
use \PDO;
use \PDOException;
use \PDOStatement;

/**
 * DbConnection class
 *
 * @package    YapepBase
 * @subpackage Database
 */
abstract class DbConnection {

    /**
     * Stores the connection instance
     *
     * @var \PDO
     */
    protected $connection;

    /**
     * Stores the connedtion name
     *
     * @var string
     */
    protected $connectionName;

    /**
     * Stores the number of open transactions.
     *
     * @var int
     */
    protected $transactionCount = 0;

    /**
     * Stores whether the current transaction has failed.
     *
     * @var bool
     */
    protected $transactionFailed = false;

    /**
     * Stores the parameter prefix.
     *
     * @var string
     */
    protected $paramPrefix = '';

    /**
     * Constructor
     *
     * @param array  $configuration    The configuration for the parameters.
     * @param string $connectionName   The name of the connection.
     * @param string $paramPrefix      The prefix for the bound parameters.
     *
     * @throws DatabaseException   On connection errors.
     */
    public function __construct(array $configuration, $connectionName, $paramPrefix = '') {
        $this->connectionName = $connectionName;
        $this->paramPrefix = $paramPrefix;
        try {
            $this->connect($configuration);
        } catch (PDOException $exception) {
            throw new DatabaseException($exception->getMessage(),0, $exception);
        }
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Opens the connection
     *
     * @param array $configuration   The configuration for the connection
     */
    abstract protected function connect(array $configuration);

    /**
     * Runs a query and returns the result object.
     *
     * @param string $query    The query to execute.
     * @param array  $params   The parameters for the query.
     *
     * @return PDOStatement   The result of the query.
     *
     * @throws DatabaseException   On execution errors.
     *
     * @todo   Replace PDOStatement with generic DbResult
     */
    public function query($query, array $params = array()) {
        try {
            $statement = $this->connection->prepare($query);
            foreach ($params as $key=>$value) {
                $statement->bindValue(':' . $this->paramPrefix . $key, $value, $this->getParamType($value));
            }
            $statement->execute();
            return $statement;
        }
        catch (PDOException $exception) {
            $this->transactionFailed = true;
            throw new DatabaseException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * Returns the PDO data type for the specified value.
     *
     * Also casts the specified value if it's necessary.
     *
     * @param mixed $value   The value to examine.
     *
     * @return int   The PDO data type.
     */
    protected function getParamType(&$value) {
        if (is_integer($value) || is_float($value)) {
            return PDO::PARAM_INT;
        } elseif (is_null($value)) {
            return PDO::PARAM_NULL;
        } elseif (is_bool($value)) {
            return PDO::PARAM_BOOL;
        } else {
            $value = (string)$value;
            return PDO::PARAM_STR;
        }
    }

    /**
     * Begins a transaction.
     *
     * If there already is an open transaction, it just increments the transaction counter.
     *
     * @return int   The number of open transactions.
     */
    public function beginTransaction() {
        if (0 == $this->transactionCount) {
            $this->connection->beginTransaction();
            $this->transactionFailed = false;
        }
        return ++$this->transactionCount;
    }

    /**
     * Completes (commits or rolls back) a transaction.
     *
     * If there is more then 1 open transaction, it only decrements the transaction count by one, and returns the
     * current transaction status. It is possible for these transactions to fail and be eventually rolled back,
     * if any further statements fail.
     *
     * @return bool   TRUE if the transaction was committed, FALSE if it was rolled back.
     */
    public function completeTransaction() {
        $this->transactionCount--;
        if (0 == $this->transactionCount) {
            if ($this->transactionFailed) {
                $this->connection->rollBack();
                return false;
            } else {
                return $this->connection->commit();
            }
        }
        return $this->transactionFailed;
    }

    /**
     * Sets a transaction's status to failed.
     */
    public function failTransaction() {
        $this->transactionFailed = true;
    }

    /**
     * Returns the quoted version of the specified value.
     *
     * Do not use this function to quote data in a query, use the bound parameters instead. {@see self::query()}
     *
     * @param mixed $value   The value to quote.
     *
     * @return string   The quoted value.
     */
    public function quote($value) {
        return $this->connection->quote($value, $this->getParamType($value));
    }
}