<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Database;

use YapepBase\Database\ConnectionHandler;
use YapepBase\Database\IConnection;
use YapepBase\Helper\DateHelper;

class ConnectionHandlerStub extends ConnectionHandler
{
    public function __construct(IConnection $connection, DateHelper $dateHelper)
    {
        $this->dateHelper = $dateHelper;
        $this->connection = $connection;
    }
}
