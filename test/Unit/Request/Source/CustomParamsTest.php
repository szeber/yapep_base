<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Request\Source;

use YapepBase\Request\Source\CustomParams;
use YapepBase\Test\Unit\TestAbstract;

class CustomParamsTest extends TestAbstract
{
    public function testSet_shouldSetGivenValue()
    {
        $customParams = new CustomParams(['first' => 1]);
        $customParams->set('second', 2);

        $result = $customParams->toArray();

        $expectedResult = [
            'first'  => 1,
            'second' => 2,
        ];

        $this->assertSame($expectedResult, $result);
    }
}
