<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Database\Exception;

use YapepBase\DataBase\Exception\Exception;
use YapepBase\Test\Unit\TestAbstract;

class ExceptionTest extends TestAbstract
{
    public function pdoMessageProvider(): array
    {
        return [
            ['un matchable', 'un matchable', 0],
            ['SQLSTATE[text] Message', 'SQLSTATE[text] Message', 0],
            ['SQLSTATE[12] Message', 'Message', 12],
        ];
    }

    /**
     * @dataProvider pdoMessageProvider
     */
    public function testCreateByPdoException_shouldParseMessageProperly(string $pdoMessage, string $expectedMessage, int $expectedCode)
    {
        $pdoException = new \PDOException($pdoMessage);
        $exception = Exception::createByPdoException($pdoException);

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame($expectedCode, $exception->getCode());
    }

    public function testThrowByPdoException_shouldThrowExceptionWithParsedPdoMessage()
    {
        $pdoException = new \PDOException('SQLSTATE[12] Message');
        $expectedException = new Exception('Message', 12);

        $this->expectExceptionObject($expectedException);
        Exception::throwByPdoException($pdoException);
    }
}
