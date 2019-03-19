<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Storage\Key;

use YapepBase\Storage\Key\Generator;
use YapepBase\Test\Unit\TestAbstract;

class GeneratorTest extends TestAbstract
{
    public function keyProvider(): array
    {
        return [
            'no prefix or suffix' => ['key', '',       '',       'key'],
            'no suffix'           => ['key', 'prefix', '',       'prefixkey'],
            'no prefix'           => ['key', '',       'suffix', 'keysuffix'],
        ];
    }

    /**
     * @dataProvider keyProvider
     */
    public function testGenerateWhenNotHashing_shouldNotHashKey(string $key, string $prefix, string $suffix, string $expectedResult)
    {
        $generator = new Generator(false, $prefix, $suffix);

        $result = $generator->generate($key);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider keyProvider
     */
    public function testGenerateWhenHashing_shouldNotHashKey(string $key, string $prefix, string $suffix, string $expectedResult)
    {
        $generator = new Generator(true, $prefix, $suffix);

        $result = $generator->generate($key);

        $this->assertEquals(md5($expectedResult), $result);
    }
}
