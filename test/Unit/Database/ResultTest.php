<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Database;

use Mockery\MockInterface;
use PDO;
use YapepBase\Database\Result;
use YapepBase\Test\Unit\TestAbstract;

class ResultTest extends TestAbstract
{
    /** @var \PDOStatement|MockInterface */
    private $statement;
    /** @var string  */
    private $className = 'class';

    protected function setUp(): void
    {
        parent::setUp();
        $this->statement = \Mockery::mock(\PDOStatement::class);
    }

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
        $result    = new Result($this->statement);

        $this->expectFetch($fetchResult);
        $fetchResult = $result->fetch();

        $this->assertSame($expectedResult, $fetchResult);
    }

    public function fetchObjectResultProvider(): array
    {
        $object = new \stdClass();
        return [
            [$object, $object],
            [false, null]
        ];
    }

    /**
     * @dataProvider fetchObjectResultProvider
     */
    public function testFetchClass_shouldReturnFetchedDataAsObject($fetchResult, $expectedResult)
    {
        $result    = new Result($this->statement);

        $this->expectFetchObject($fetchResult);
        $fetchResult = $result->fetchClass($this->className);

        $this->assertSame($expectedResult, $fetchResult);
    }

    public function testFetchAll_shouldReturnRetrievedAssociativeData()
    {
        $result    = new Result($this->statement);
        $expectedResult = [['test' => 1]];

        $this->expectFetchAll($expectedResult);
        $fetchResult = $result->fetchAll();

        $this->assertSame($expectedResult, $fetchResult);
    }

    /**
     * @dataProvider fetchResultProvider
     */
    public function testFetchColumn_shouldReturnRetrievedDataOrNull($fetchResult, $expectedResult)
    {
        $result      = new Result($this->statement);
        $columnIndex = 2;

        $this->expectFetchColumn($columnIndex, $fetchResult);
        $fetchResult = $result->fetchColumn($columnIndex);

        $this->assertSame($expectedResult, $fetchResult);
    }

    public function testFetchColumnAll_shouldReturnColumn()
    {
        $result      = new Result($this->statement);
        $columnIndex = 2;

        $this->expectFetchColumn($columnIndex, 'one');
        $this->expectFetchColumn($columnIndex, 'two');
        $this->expectFetchColumn($columnIndex, false);
        $fetchResult = $result->fetchColumnAll($columnIndex);

        $this->assertSame(['one', 'two'], $fetchResult);
    }

    public function testFetchClassAll_shouldReturnRetrievedDataAsObjects()
    {
        $result      = new Result($this->statement);
        $fetchResult = [['test' => 1]];
        $className   = \stdClass::class;

        $this->expectFetchAllClass($className, $fetchResult);
        $result = $result->fetchAllClass($className);

        $this->assertSame($fetchResult, $result);
    }

    private function expectFetch($expectedResult)
    {
        $this->statement
            ->shouldReceive('fetch')
            ->once()
            ->andReturn($expectedResult);
    }

    private function expectFetchAll($expectedResult)
    {
        $this->statement
            ->shouldReceive('fetchAll')
            ->once()
            ->with(\PDO::FETCH_ASSOC)
            ->andReturn($expectedResult);
    }

    private function expectFetchColumn(int $columnIndex, $expectedResult)
    {
        $this->statement
            ->shouldReceive('fetchColumn')
            ->once()
            ->with($columnIndex)
            ->andReturn($expectedResult);
    }

    private function expectFetchAllClass(string $className, $expectedResult)
    {
        $this->statement
            ->shouldReceive('setFetchMode')
                ->once()
                ->with(PDO::FETCH_CLASS, $className)
                ->getMock()
            ->shouldReceive('fetchAll')
                ->once()
                ->andReturn($expectedResult);
    }

    private function expectFetchObject($expectedResult): void
    {
        $this->statement
            ->shouldReceive('fetchObject')
            ->once()
            ->with($this->className)
            ->andReturn($expectedResult);
    }
}
