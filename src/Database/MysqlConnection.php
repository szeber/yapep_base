<?php
declare(strict_types=1);

namespace YapepBase\Database;

use PDO;
use YapepBase\DataBase\Exception\Exception;

/**
 * MySQL database connection implementation.
 */
class MysqlConnection implements IConnection, IHandlesTransactions
{
    /** @var string */
    protected $dsn;
    /** @var string */
    protected $host;
    /** @var string */
    protected $database;
    /** @var int */
    protected $port;
    /** @var string */
    protected $username;
    /** @var string */
    protected $password;
    /** @var array */
    protected $pdoOptions = [];
    /** @var string */
    protected $charset = 'utf8mb4';
    /** @var string */
    protected $sqlMode = 'TRADITIONAL';
    /** @var string|null */
    protected $timezone;
    /** @var PDO */
    protected $pdoConnection;

    public function __construct(string $host, string $database, int $port, string $username, string $password, array $pdoOptions = [])
    {
        $this->host       = $host;
        $this->port       = $port;
        $this->database   = $database;
        $this->username   = $username;
        $this->password   = $password;
        $this->pdoOptions = $pdoOptions;

        $this->dsn = 'mysql:host=' . $this->host
            . ';dbname=' . $this->database
            . ';port=' . $this->port;
    }

    /**
     * @throws Exception
     */
    public function connect(): void
    {
        try {
            $this->pdoConnection = $this->getConnection();
        }
        catch (\PDOException $e) {
            Exception::throwByPdoException($e);
        }

        $this->pdoConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function disconnect(): void
    {
        $this->pdoConnection = null;
    }

    public function getDsn(): string
    {
       return $this->dsn;
    }

    /**
     * @return string[]
     */
    public function getInitialiseQueries(): array
    {
        $queries = [
            'SET NAMES ' . $this->charset,
            'SET @@SESSION.sql_mode = ' . $this->sqlMode,
        ];

        if (!empty($this->timezone)) {
            $queries[] =  'SET time_zone = ' . $this->timezone;
        }

        return $queries;
    }

    public function prepareStatement(string $query): \PDOStatement
    {
        return $this->pdoConnection->prepare($query);
    }

    public function getLastInsertId(?string $name): string
    {
        return $this->pdoConnection->lastInsertId($name);
    }

    public function beginTransaction(): bool
    {
        return $this->pdoConnection->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdoConnection->commit();
    }

    public function rollback(): bool
    {
        return $this->pdoConnection->rollBack();
    }

    public function setCharset(string $charset): self
    {
        $this->charset = $charset;
        return $this;
    }

    public function setSqlMode(string $sqlMode): self
    {
        $this->sqlMode = $sqlMode;
        return $this;
    }

    public function setTimezone(?string $timezone): self
    {
        $this->timezone = $timezone;
        return $this;
    }

    protected function getConnection(): PDO
    {
        return new PDO($this->dsn, $this->username, $this->password, $this->pdoOptions);
    }
}
