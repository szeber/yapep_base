<?php
declare(strict_types=1);

namespace YapepBase\Database;

use PDO;
use PDOStatement;

/**
 * Database result class.
 *
 * Wrapper for the PDOResult object.
 */
class Result
{
    /** @var \PDOStatement */
    protected $statement;

    public function __construct(PDOStatement $statement)
    {
        $this->statement = $statement;
    }

    /**
     * Returns one row from the result set, or FALSE if there are no more rows.
     */
    public function fetch(): ?array
    {
        $result = $this->statement->fetch(PDO::FETCH_ASSOC);
        return $result === false
            ? null
            : $result;
    }

    /**
     * Returns one row represented in the given class.
     *
     * @return null|object
     */
    public function fetchClass(string $className): ?object
    {
        $result = $this->statement->fetchObject($className);
        return $result === false
            ? null
            : $result;
    }

    /**
     * Returns every row as a dictionary (key => value pairs)
     *
     * The first column will be the key and everything else a value as an associative array
     */
    public function fetchDictionary(): array
    {
        return $this->statement->fetchAll(PDO::FETCH_UNIQUE);
    }

    /**
     * Returns a column from the result set.
     *
     * @return mixed|null
     */
    public function fetchColumn(int $columnIndex = 0)
    {
        $result = $this->statement->fetchColumn($columnIndex);

        return $result === false
            ? null
            : $result;
    }

    /**
     * Returns two Columns as a dictionary (key => value pairs).
     *
     * The first column will be the key and the second one the value.
     */
    public function fetchColumnDictionary(): array
    {
        return $this->statement->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Return a simple 1 dimensional array which stores the required columns value from every row.
     */
    public function fetchColumnAll(int $columnIndex = 0): array
    {
        $result = [];
        while (($columnValue = $this->statement->fetchColumn($columnIndex)) !== false) {
            $result[] = $columnValue;
        }

        return $result;
    }

    /**
     * Returns all rows from the result set.
     */
    public function fetchAll(): array
    {
        return $this->statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns all the rows represented in the given class.
     *
     * @return object[]
     */
    public function fetchAllClass(string $className): array
    {
        $this->statement->setFetchMode(PDO::FETCH_CLASS, $className);

        return $this->statement->fetchAll();
    }

    /**
     * Returns the number of rows affected by the last INSERT, DELETE or UPDATE statement.
     */
    public function getAffectedRowCount(): int
    {
        return $this->statement->rowCount();
    }

    public function getStatement(): PDOStatement
    {
        return $this->statement;
    }
}
