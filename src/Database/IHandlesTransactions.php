<?php
declare(strict_types=1);

namespace YapepBase\Database;

interface IHandlesTransactions
{
    /**
     * Begins a transaction.
     */
    public function beginTransaction(): bool;

    /**
     * Commits the transaction
     */
    public function commit(): bool;

    /**
     * Rolls back the transaction
     */
    public function rollback(): bool;
}
