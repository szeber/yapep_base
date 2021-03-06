<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Database;

use YapepBase\DataBase\Exception\Exception;
use YapepBase\Database\MysqlConnection;
use YapepBase\Test\Unit\TestAbstract;

class MysqlConnectionTest extends TestAbstract
{
    /** @var MysqlConnectionStub */
    private $connection;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub

        $this->connection = new MysqlConnectionStub('host', 'db', 1, 'user', 'pass');
    }

    public function testConstruct_shouldCreateProperDsn()
    {
        $this->assertSame('mysql:host=host;dbname=db;port=1', $this->connection->getDsn());
    }

    public function testConnectWhenThrowsException_shouldTransformToDbException()
    {
        $connection = $this->expectConnectionThrowsException('SQLSTATE[1] Test');

        $this->expectExceptionObject(new Exception('Test', 1));
        $connection->connect();
    }

    public function testConnect_shouldSetErrorMode()
    {
        $this->expectErrorModeSet();

        $this->connection->connect();
    }

    public function testGetInitialiseQueriesWhenDefault_shouldReturnModeAndCharsetSetterQueries()
    {
        $queries = $this->connection->getInitialiseQueries();

        $expectedQueries = [
            "SET NAMES utf8mb4",
            "SET @@SESSION.sql_mode = TRADITIONAL",
        ];

        $this->assertEquals($expectedQueries, $queries);
    }

    public function testGetInitialiseQueriesWhenModeChanged_shouldReturnGivenModeSetter()
    {
        $this->connection->setSqlMode('mode');
        $queries = $this->connection->getInitialiseQueries();

        $this->assertCount(2, $queries);
        $this->assertContains('SET @@SESSION.sql_mode = mode', $queries);
    }

    public function testGetInitialiseQueriesWhenCharsetChanged_shouldReturnGivenCharsetSetter()
    {
        $this->connection->setCharset('charset');
        $queries = $this->connection->getInitialiseQueries();

        $this->assertCount(2, $queries);
        $this->assertContains('SET NAMES charset', $queries);
    }

    public function testGetInitialiseQueriesWhenTimezoneSet_shouldReturnTimezoneSetter()
    {
        $this->connection->setTimezone('tz');
        $queries = $this->connection->getInitialiseQueries();

        $this->assertCount(3, $queries);
        $this->assertContains('SET time_zone = tz', $queries);
    }

    private function expectConnectionThrowsException(string $expectedMessage): MysqlConnection
    {
        return \Mockery::mock(MysqlConnection::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getConnection')
            ->andThrow(new \PDOException($expectedMessage))
            ->getMock();
    }

    private function expectErrorModeSet(): void
    {
        $pdoConnection = \Mockery::mock(\PDO::class)
            ->shouldReceive('setAttribute')
            ->once()
            ->with(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION)
            ->getMock();

        $this->connection->connection = $pdoConnection;
    }
}
