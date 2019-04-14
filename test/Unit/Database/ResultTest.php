<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Database;

use Mockery\MockInterface;
use PDO;
use YapepBase\Database\Result;
use YapepBase\Test\Unit\TestAbstract;

class ResultTest extends TestAbstract
{
    public function fetchResultProvider(): array
    {
        return [
            [['test'], ['test']],
            [false, null]
        ];
    }

    /**
     * @dataProvider fetchResultProvider
     */
    public function testFetch_shouldReturnRetrievedDataOrNull($fetchResult, $expectedResult)
    {
        $statement = \Mockery::mock(\PDOStatement::class);
        $result    = new Result($statement);

        $this->expectFetch($statement, $fetchResult);
        $fetchResult = $result->fetch();

        $this->assertSame($expectedResult, $fetchResult);
    }

    public function testFetchAll_shouldReturnRetrievedAssociativeData()
    {
        $statement = \Mockery::mock(\PDOStatement::class);
        $result    = new Result($statement);
        $expectedResult = [['test' => 1]];

        $this->expectFetchAll($statement, $expectedResult);
        $fetchResult = $result->fetchAll();

        $this->assertSame($expectedResult, $fetchResult);
    }

    /**
     * @dataProvider fetchResultProvider
     */
    public function testFetchColumn_shouldReturnRetrievedDataOrNull($fetchResult, $expectedResult)
    {
        $statement   = \Mockery::mock(\PDOStatement::class);
        $result      = new Result($statement);
        $columnIndex = 2;

        $this->expectFetchColumn($statement, $columnIndex, $fetchResult);
        $fetchResult = $result->fetchColumn($columnIndex);

        $this->assertSame($expectedResult, $fetchResult);
    }

    public function testFetchColumnAll_shouldReturnColumn()
    {
        $statement   = \Mockery::mock(\PDOStatement::class);
        $result      = new Result($statement);
        $columnIndex = 2;

        $this->expectFetchColumn($statement, $columnIndex, 'one');
        $this->expectFetchColumn($statement, $columnIndex, 'two');
        $this->expectFetchColumn($statement, $columnIndex, false);
        $fetchResult = $result->fetchColumnAll($columnIndex);

        $this->assertSame(['one', 'two'], $fetchResult);
    }

    public function testFetchClassAll_shouldReturnRetrievedDataAsObjects()
    {
        $statement   = \Mockery::mock(\PDOStatement::class);
        $result      = new Result($statement);
        $fetchResult = [['test' => 1]];
        $className   = \stdClass::class;

        $this->expectFetchAllClass($statement, $className, $fetchResult);
        $result = $result->fetchAllClass($className);

        $this->assertSame($fetchResult, $result);
    }

    private function expectFetch(MockInterface $statement, $expectedResult)
    {
        $statement
            ->shouldReceive('fetch')
            ->once()
            ->andReturn($expectedResult);
    }

    private function expectFetchAll(MockInterface $statement, $expectedResult)
    {
        $statement
            ->shouldReceive('fetchAll')
            ->once()
            ->with(\PDO::FETCH_ASSOC)
            ->andReturn($expectedResult);
    }

    private function expectFetchColumn(MockInterface $statement, int $columnIndex, $expectedResult)
    {
        $statement
            ->shouldReceive('fetchColumn')
            ->once()
            ->with($columnIndex)
            ->andReturn($expectedResult);
    }

    private function expectFetchAllClass(MockInterface $statement, string $className, $expectedResult)
    {
        $statement
            ->shouldReceive('setFetchMode')
                ->once()
                ->with(PDO::FETCH_CLASS, $className)
                ->getMock()
            ->shouldReceive('fetchAll')
                ->once()
                ->andReturn($expectedResult);
    }
}
