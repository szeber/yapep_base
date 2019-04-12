<?php
declare(strict_types=1);

namespace YapepBase\Database;

use PDO;
use YapepBase\DataBase\Exception\Exception;

/**
 * Sqlite database connection implementation.
 */
class SqliteConnection implements IConnection
{
    /** @var string */
    protected $dsn;
    /** @var string  */
    protected $path;
    /** @var array */
    protected $pdoOptions = [];
    /** @var PDO */
    protected $pdoConnection;

    public function __construct(string $dsn, string $path, array $pdoOptions = [])
    {
        $this->dsn        = $dsn;
        $this->path       = $path;
        $this->pdoOptions = $pdoOptions;
        $this->dsn        = 'sqlite:' . $this->path;
    }

    public function connect(): void
    {
        try {
            $this->pdoConnection = new PDO($this->dsn, '', '', $this->pdoOptions);
        }
        catch (\PDOException $e) {
            Exception::throwByPdoException($e);
        }
    }

    public function disconnect(): void
    {
        $this->pdoConnection = null;
    }

    public function getDsn(): string
    {
       return $this->dsn;
    }

    public function getInitialiseQueries(): array
    {
        return [];
    }

    public function prepareStatement(string $query): \PDOStatement
    {
        return $this->pdoConnection->prepare($query);
    }

    public function getLastInsertId(?string $name): string
    {
        return $this->pdoConnection->lastInsertId($name);
    }
}
