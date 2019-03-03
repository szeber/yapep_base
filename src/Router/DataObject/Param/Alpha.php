<?php
declare(strict_types = 1);

namespace YapepBase\Router\DataObject\Param;

class Alpha extends ParamAbstract
{
    public function getPattern(): string
    {
        return '[a-zA-Z]+';
    }
}
