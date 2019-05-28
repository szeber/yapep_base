<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Database;

use PDO;
use YapepBase\Database\MysqlConnection;

class MysqlConnectionStub extends MysqlConnection
{
    /** @var PDO */
    public $connection;

    protected function getConnection(): PDO
    {
        return $this->connection;
    }
}
