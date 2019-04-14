<?php
declare(strict_types=1);

namespace YapepBase\Database;

use PDO;
use PDOException;

use YapepBase\Application;
use YapepBase\DataBase\Exception\Exception;
use YapepBase\Debug\IDataHandlerRegistry;
use YapepBase\Debug\Item\SqlQuery;
use YapepBase\Helper\DateHelper;

/**
 * Base class for database connections.
 */
class ConnectionHandler implements IConnectionHandler
{
    /** @var DateHelper */
    protected $dateHelper;

    /** @var IConnection|IHandlesTransactions */
    protected $connection;

    /** @var string */
    protected $connectionName;

    /** @var int */
    protected $openTransactionCount = 0;

    /** @var bool */
    protected $transactionFailed = false;

    /** @var string */
    protected $paramPrefix = '';

    public function __construct(IConnection $connection, DateHelper $dateHelper)
    {
        $this->dateHelper = $dateHelper;
        $this->connection = $connection;

        $this->connect();
    }

    public function setParamPrefix(string $paramPrefix)
    {
        $this->paramPrefix = $paramPrefix;
        return $this;
    }

    public function getParamPrefix(): string
    {
        return $this->paramPrefix;
    }

    public function query(string $query, array $params = []): Result
    {
        $this->getCaller($callerClass, $callerMethod);

        $debugItem = (new SqlQuery($this->dateHelper, $this->connection->getDsn(), $query, $params, $callerClass, $callerMethod));
        $statement = $this->executeQuery($query, $params);

        $debugItem->setFinished();

        $this->getDebugDataHandlerRegistry()->addSqlQuery($debugItem);

        return new Result($statement);
    }

    public function getLastInsertId(?string $name = null): string
    {
        try {
            return $this->connection->getLastInsertId($name);
        }
        catch (PDOException $exception) {
            Exception::throwByPdoException($exception);
        }
    }

    public function escapeWildcards(string $string, string $escapeCharacter = '\\'): string
    {
        return preg_replace(
            '/([_%' . preg_quote($escapeCharacter, '/') . '])/',
            addcslashes($escapeCharacter, '$\\') . '$1',
            $string
        );
    }

    public function beginTransaction(): int
    {
        $this->requireTransactionHandling();

        if ($this->openTransactionCount == 0) {
            $this->connection->beginTransaction();
            $this->transactionFailed = false;
        }

        return ++$this->openTransactionCount;
    }

    public function completeTransaction(): bool
    {
        $this->requireTransactionHandling();

        $this->openTransactionCount--;

        if (0 == $this->openTransactionCount) {
            if ($this->transactionFailed) {
                $this->connection->rollBack();

                return false;
            }
            else {
                return $this->connection->commit();
            }
        }

        return $this->transactionFailed;
    }

    public function failTransaction(): void
    {
        $this->transactionFailed = true;
    }

    public function isTransactionFailed(): bool
    {
        return $this->transactionFailed;
    }

    private function connect(): void
    {
        $this->connection->connect();

        foreach ($this->connection->getInitialiseQueries() as $query) {
            $this->query($query);
        }
    }

    /**
     * @throws Exception
     */
    private function executeQuery(string $query, array $params = []): \PDOStatement
    {
        try {
            $statement = $this->connection->prepareStatement($query);

            foreach ($params as $key => $value) {
                $statement->bindValue(':' . $this->paramPrefix . $key, $value, $this->castParamAndGetType($value));
            }
            $statement->execute();

            return $statement;
        } catch (PDOException $exception) {
            $this->transactionFailed = true;
            Exception::throwByPdoException($exception);
        }
    }

    private function castParamAndGetType(&$value): int
    {
        if (is_integer($value)) {
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

    private function getDebugDataHandlerRegistry(): IDataHandlerRegistry
    {
        return Application::getInstance()->getDiContainer()->getDebugDataHandlerRegistry();
    }

    private function getCaller(?string &$className, ?string &$methodName): void
    {
        $className  = '';
        $methodName = '';
        $backtrace  = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4);

        foreach ($backtrace as $caller) {
            if ($caller['class'] !== __CLASS__) {
                $className  = $caller['class'];
                $methodName = $caller['function'];
                return;
            }
        }
    }

    /**
     * @throws Exception
     */
    private function requireTransactionHandling(): void
    {
        if (!($this->connection instanceof IHandlesTransactions)) {
            throw new Exception('Connection does not handle transactions');
        }
    }
}
