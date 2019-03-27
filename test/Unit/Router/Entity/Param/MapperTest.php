<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Router\Entity\Param;

use YapepBase\Exception\InvalidArgumentException;
use YapepBase\Router\Entity\Param\Mapper;

class MapperTest extends TestAbstract
{
    public function testGetClassByTypeWhenNonExistentTypeGiven_shouldThrowException()
    {
        $this->expectException(InvalidArgumentException::class);
        Mapper::getClassByType('invalid');
    }

    public function testGetTypeByClassWhenNonExistentClassGiven_shouldThrowException()
    {
        $this->expectException(InvalidArgumentException::class);
        Mapper::getTypeByClass('invalid');
    }
}
