<?php
declare(strict_types=1);

namespace YapepBase\Database;

use YapepBase\DataBase\Exception\Exception;

/**
 * Base class for database connections.
 */
interface IConnectionHandler
{
    /**
     * Sets the prefix of parameters.
     *
     * @return static
     */
    public function setParamPrefix(string $paramPrefix);

    /**
     * Returns the parameter prefix.
     */
    public function getParamPrefix(): string;

    /**
     * @throws Exception
     */
    public function query(string $query, array $params = []): Result;

    /**
     * Returns the last insert id for the connection.
     *
     * @throws Exception
     */
    public function getLastInsertId(?string $name = null): string;

    /**
     * Returns the provided string, with all wildcard characters escaped.
     *
     * This method should be used to escape the string part in the "LIKE 'string' ESCAPE 'escapeCharacter'" statement.
     */
    public function escapeWildcards(string $string, string $escapeCharacter = '\\'): string;

    /**
     * Begins a transaction.
     *
     * If there already is an open transaction, it just increments the transaction counter.
     *
     * @throws Exception
     */
    public function beginTransaction(): int;

    /**
     * Completes (commits or rolls back) a transaction.
     *
     * If there is more then 1 open transaction, it only decrements the transaction count by one, and returns the
     * current transaction status. It is possible for these transactions to fail and be eventually rolled back,
     * if any further statements fail.
     *
     * @throws Exception
     */
    public function completeTransaction(): bool;

    /**
     * Sets a transaction's status to failed.
     */
    public function failTransaction(): void;

    /**
     * Tells if the transaction is failed
     */
    public function isTransactionFailed(): bool;
}
