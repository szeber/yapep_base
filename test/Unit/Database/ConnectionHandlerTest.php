<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Database;

use Mockery\MockInterface;
use YapepBase\Database\ConnectionHandler;
use YapepBase\DataBase\Exception\Exception;
use YapepBase\Database\IConnection;
use YapepBase\Database\IHandlesTransactions;
use YapepBase\Database\MysqlConnection;
use YapepBase\Debug\DataHandlerRegistry;
use YapepBase\Debug\ICanReturnItems;
use YapepBase\Debug\Item\SqlQuery;
use YapepBase\Helper\DateHelper;

class ConnectionHandlerTest extends TestAbstract
{
    /** @var string */
    protected $dsn = 'dsn';
    /** @var DataHandlerRegistry */
    protected $debugDataHandlerRegistry;
    /** @var string */
    protected $query = 'SELECT * FROM test WHERE id = :id';
    /** @var \PDOException */
    protected $pdoException;
    /** @var Exception */
    protected $dbException;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initDebugHandler();

        $this->pdoException = new \PDOException('SQLSTATE[123] Message');
        $this->dbException  = Exception::createByPdoException($this->pdoException);;
    }

    private function initDebugHandler(): void
    {
        $this->debugDataHandlerRegistry = new DataHandlerRegistry(new DateHelper());
        $this->pimpleContainer->setDebugDataHandlerRegistry($this->debugDataHandlerRegistry);
    }

    public function testConstruct_shouldConnectAndInit()
    {
        $initQuery  = 'SET NAMES';
        $connection = \Mockery::mock(IConnection::class);

        $statement = \Mockery::mock(\PDOStatement::class);
        $this->expectPrepareStatement($connection, $statement, $initQuery);
        $this->expectGetInitQueries($connection, [$initQuery]);
        $this->expectStatementExecute($statement);
        $this->expectGetDsn($connection);

        new ConnectionHandler($connection, new DateHelper());
    }

    public function paramTypeProvider(): array
    {
        return [
            [1, \PDO::PARAM_INT],
            [null, \PDO::PARAM_NULL],
            [true, \PDO::PARAM_BOOL],
            ['test', \PDO::PARAM_STR],
            [0.1, \PDO::PARAM_STR],
        ];
    }

    /**
     * @dataProvider paramTypeProvider
     */
    public function testQuery_shouldBindParamsProperlyAndExecute($paramValue, int $pdoParamType)
    {
        $paramName         = 'param';
        $connection        = \Mockery::mock(IConnection::class);
        $statement         = \Mockery::mock(\PDOStatement::class);
        $connectionHandler = new ConnectionHandlerStub($connection, new DateHelper());

        $this->expectPrepareStatement($connection, $statement, $this->query);
        $this->expectStatementBindValue($statement, $paramName, $paramValue, $pdoParamType);
        $this->expectStatementExecute($statement);
        $this->expectGetDsn($connection);

        $result = $connectionHandler->query($this->query, [$paramName => $paramValue]);

        $this->assertSame($statement, $result->getStatement());
    }

    public function testQuery_shouldStoreDebug()
    {
        $params            = ['test' => 1];
        $connection        = \Mockery::mock(IConnection::class);
        $connectionHandler = new ConnectionHandlerStub($connection, new DateHelper());
        $statement         = new \PDOStatement();

        $this->expectPrepareStatement($connection, $statement, $this->query);
        $this->expectGetDsn($connection);
        $this->expectDebugItemStored($params, __FUNCTION__);

        $connectionHandler->query($this->query, $params);
    }

    public function testGetLastInsertId_shouldUseConnection()
    {
        $connection        = \Mockery::mock(IConnection::class);
        $connectionHandler = new ConnectionHandlerStub($connection, new DateHelper());
        $name              = 'test';
        $expectedId        = '12';

        $this->expectGetLastInsertId($connection, $name, $expectedId);

        $result = $connectionHandler->getLastInsertId($name);

        $this->assertSame($expectedId, $result);
    }

    public function testGetLastInsertIdWhenFails_shouldThrowDbException()
    {
        $connection        = \Mockery::mock(IConnection::class);
        $connectionHandler = new ConnectionHandlerStub($connection, new DateHelper());

        $this->expectGetLastInsertIdFails($connection);

        $this->expectExceptionObject($this->dbException);
        $connectionHandler->getLastInsertId();
    }

    public function testEscapeWildcards_shouldEscapeEveryWildCard()
    {
        $connection        = \Mockery::mock(IConnection::class);
        $connectionHandler = new ConnectionHandlerStub($connection, new DateHelper());

        $result = $connectionHandler->escapeWildcards('_%fsad_%');

        $this->assertSame('\_\%fsad\_\%', $result);
    }

    public function testBeginTransactionWhenConnectionNotHandlingTransactions_shouldThrowException()
    {
        $connection        = \Mockery::mock(IConnection::class);
        $connectionHandler = new ConnectionHandlerStub($connection, new DateHelper());

        $this->expectException(Exception::class);
        $connectionHandler->beginTransaction();
    }

    public function testBeginTransaction_shouldStartTransaction()
    {
        $connection        = \Mockery::mock(MysqlConnection::class);
        $connectionHandler = new ConnectionHandlerStub($connection, new DateHelper());

        $this->expectTransactionStarted($connection);
        $transactionCount = $connectionHandler->beginTransaction();

        $this->assertSame(1, $transactionCount);
    }

    public function testBeginTransactionWhenCalledMultipleTimes_shouldStartTransactionOnceOnly()
    {
        $connection        = \Mockery::mock(MysqlConnection::class);
        $connectionHandler = new ConnectionHandlerStub($connection, new DateHelper());

        $this->expectTransactionStarted($connection);
        $connectionHandler->beginTransaction();
        $transactionCount = $connectionHandler->beginTransaction();

        $this->assertSame(2, $transactionCount);
    }

    public function testBeginTransaction_shouldResetFailStatus()
    {
        $connection        = \Mockery::mock(MysqlConnection::class);
        $connectionHandler = new ConnectionHandlerStub($connection, new DateHelper());

        $connectionHandler->failTransaction();

        $this->expectTransactionStarted($connection);
        $connectionHandler->beginTransaction();

        $this->assertSame(false, $connectionHandler->isTransactionFailed());
    }

    public function testCompleteTransactionWhenNotConnectionNotHandlingTransactions_shouldThrowException()
    {
        $connection        = \Mockery::mock(IConnection::class);
        $connectionHandler = new ConnectionHandlerStub($connection, new DateHelper());

        $this->expectException(Exception::class);
        $connectionHandler->completeTransaction();
    }

    public function testCompleteTransactionWhenNotStartedYet_shouldDoNothing()
    {
        $connection        = \Mockery::mock(MysqlConnection::class);
        $connectionHandler = new ConnectionHandlerStub($connection, new DateHelper());

        $result = $connectionHandler->completeTransaction();

        $this->assertSame($connectionHandler->isTransactionFailed(), $result);
    }

    public function testCompleteTransactionWhenTransactionFailed_shouldRollBack()
    {
        $connection        = \Mockery::mock(MysqlConnection::class);
        $connectionHandler = new ConnectionHandlerStub($connection, new DateHelper());

        $this->expectTransactionStarted($connection);
        $connectionHandler->beginTransaction();
        $connectionHandler->failTransaction();

        $this->expectTransactionRolledBack($connection);
        $result = $connectionHandler->completeTransaction();

        $this->assertFalse($result);
    }

    public function testCompleteTransactionWhenTransactionSuccessful_shouldCommit()
    {
        $connection        = \Mockery::mock(MysqlConnection::class);
        $connectionHandler = new ConnectionHandlerStub($connection, new DateHelper());

        $this->expectTransactionStarted($connection);
        $connectionHandler->beginTransaction();

        $this->expectTransactionCommitted($connection, true);
        $result = $connectionHandler->completeTransaction();

        $this->assertTrue($result);
    }

    public function testCompleteTransactionWhenStartedMultipleTimes_shouldJustDecreaseCount()
    {
        $connection        = \Mockery::mock(MysqlConnection::class);
        $connectionHandler = new ConnectionHandlerStub($connection, new DateHelper());

        $this->expectTransactionStarted($connection);
        $connectionHandler->beginTransaction();
        $connectionHandler->beginTransaction();
        $connectionHandler->completeTransaction();

        $this->expectTransactionCommitted($connection, true);
        $connectionHandler->completeTransaction();
    }

    private function expectGetInitQueries(MockInterface $connection, array $initQueries): void
    {
        $connection
            ->shouldReceive('connect')
                ->once()
                ->getMock()
            ->shouldReceive('getInitialiseQueries')
                ->once()
                ->andReturn($initQueries);
    }

    private function expectGetDsn(MockInterface $connection): void
    {
        $connection
            ->shouldReceive('getDsn')
                ->andReturn($this->dsn)
                ->getMock();
    }

    private function expectPrepareStatement(MockInterface $connection, \PDOStatement $statement, string $query):void
    {
        $connection
            ->shouldReceive('prepareStatement')
            ->once()
            ->with($query)
            ->andReturn($statement);
    }

    private function expectGetLastInsertId(MockInterface $connection, string $name, $expectedResult)
    {
        $connection
            ->shouldReceive('getLastInsertId')
            ->once()
            ->with($name)
            ->andReturn($expectedResult);
    }

    private function expectGetLastInsertIdFails(MockInterface $connection)
    {
        $connection
            ->shouldReceive('getLastInsertId')
            ->once()
            ->andThrow($this->pdoException);
    }

    private function expectTransactionStarted(MockInterface $connection)
    {
        $connection
            ->shouldReceive('beginTransaction')
            ->once();
    }

    private function expectTransactionRolledBack(MockInterface $connection)
    {
        $connection
            ->shouldReceive('rollBack')
            ->once()
            ->andReturn(true);
    }

    private function expectTransactionCommitted(MockInterface $connection, bool $expectedResult)
    {
        $connection
            ->shouldReceive('commit')
            ->once()
            ->andReturn($expectedResult);
    }

    private function expectStatementBindValue(MockInterface $statement, string $paramName, $paramValue, int $pdoParamType): void
    {
        $statement
            ->shouldReceive('bindValue')
            ->once()
            ->with(':' . $paramName, $paramValue, $pdoParamType);
    }

    private function expectStatementExecute(MockInterface $statement): void
    {
        $statement
            ->shouldReceive('execute')
            ->once();
    }

    private function expectDebugItemStored(array $params, string $callerMethod)
    {
        $callerClass = get_class($this);

        $dataHandler = \Mockery::mock(ICanReturnItems::class)
            ->shouldReceive('addSqlQuery')
            ->once()
            ->with(\Mockery::on(function (SqlQuery $debugItem) use ($params, $callerClass, $callerMethod) {
                $this->assertSame($this->dsn, $debugItem->getDsn(), 'Dsn does not match');
                $this->assertSame($this->query, $debugItem->getQuery(), 'Query does not match');
                $this->assertSame($params, $debugItem->getParams(), 'Params does not match');
                $this->assertSame($callerClass, $debugItem->getCallerClass(), 'Caller class does not match');
                $this->assertSame($callerMethod, $debugItem->getCallerMethod(), 'Caller method does not match');
                $this->assertNotEmpty($debugItem->getFinishTime());
                return true;
            }))
            ->getMock();

        $this->debugDataHandlerRegistry->register('test', $dataHandler);
    }
}
