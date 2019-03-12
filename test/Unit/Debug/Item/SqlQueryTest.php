<?php
declare(strict_types = 1);

namespace YapepBase\Test\Unit\Debug\Item;

use Mockery;
use YapepBase\Debug\Item\SqlQuery;
use YapepBase\Helper\DateHelper;
use YapepBase\Test\Unit\TestAbstract;

class SqlQueryTest extends TestAbstract
{
    /** @var float */
    protected $currentTime = 1.2;

    public function testConstructor_shouldStoreGivenValues()
    {
        $dsn          = 'mysql:host=localhost;dbname=db';
        $query        = 'SELECT *';
        $params       = ['param1' => 1];
        $callerClass  = 'class';
        $callerMethod = 'method';

        $this->expectGetCurrentTime();
        $sqlQuery = new SqlQuery($dsn, $query, $params, $callerClass, $callerMethod);

        $this->assertSame($dsn, $sqlQuery->getDsn());
        $this->assertSame($query, $sqlQuery->getQuery());
        $this->assertSame($params, $sqlQuery->getParams());
        $this->assertSame($callerClass, $sqlQuery->getCallerClass());
        $this->assertSame($callerMethod, $sqlQuery->getCallerMethod());
    }

    protected function expectGetCurrentTime()
    {
        $dateHelper = Mockery::mock(DateHelper::class)
            ->shouldReceive('getCurrentTimestampUs')
            ->once()
            ->andReturn($this->currentTime)
            ->getMock();

        $this->pimpleContainer[DateHelper::class] = $dateHelper;
    }
}
