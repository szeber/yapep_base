<?php
declare(strict_types = 1);

namespace YapepBase\Test\Unit\Debug\Item;

use YapepBase\Debug\Item\SqlQuery;

class SqlQueryTest extends TestAbstract
{
    public function testConstructor_shouldStoreGivenValues()
    {
        $dsn          = 'mysql:host=localhost;dbname=db';
        $query        = 'SELECT *';
        $params       = ['param1' => 1];
        $callerClass  = 'class';
        $callerMethod = 'method';

        $this->expectGetCurrentTime();
        $sqlQuery = new SqlQuery($this->dateHelper, $dsn, $query, $params, $callerClass, $callerMethod);

        $this->assertSame($dsn, $sqlQuery->getDsn());
        $this->assertSame($query, $sqlQuery->getQuery());
        $this->assertSame($params, $sqlQuery->getParams());
        $this->assertSame($callerClass, $sqlQuery->getCallerClass());
        $this->assertSame($callerMethod, $sqlQuery->getCallerMethod());
    }
}
