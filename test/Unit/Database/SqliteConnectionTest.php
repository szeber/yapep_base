<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Database;

use YapepBase\Database\SqliteConnection;
use YapepBase\Test\Unit\TestAbstract;

class SqliteConnectionTest extends TestAbstract
{
    public function testConstruct_shouldCreateProperDsn()
    {
        $path = '/tmp/sql';
        $connection = new SqliteConnection($path);

        $this->assertSame('sqlite:/tmp/sql', $connection->getDsn());
    }
}
