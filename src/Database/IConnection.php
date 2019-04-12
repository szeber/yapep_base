<?php
declare(strict_types=1);

namespace YapepBase\Database;

use YapepBase\DataBase\Exception\Exception;

interface IConnection
{
    /**
     * Connects to the DB
     *
     * @throws Exception
     */
    public function connect(): void;

    /**
     * Closes the database connection.
     */
    public function disconnect(): void;

    /**
     * Returns the DSN string used to create the connection.
     */
    public function getDsn(): string;

    /**
     * Returns the queries what should be called after connection has been made.
     *
     * @return string[]
     */
    public function getInitialiseQueries(): array;

    /**
     * Prepares a statement from the given query.
     */
    public function prepareStatement(string $query): \PDOStatement;

    /**
     * Returns the id of the last inserted item.
     */
    public function getLastInsertId(?string $name): string;
}
