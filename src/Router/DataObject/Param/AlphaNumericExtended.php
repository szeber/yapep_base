<?php
declare(strict_types = 1);

namespace YapepBase\Router\DataObject\Param;

class AlphaNumericExtended extends ParamAbstract
{
    public function getPattern(): string
    {
        return '[-_0-9a-zA-Z]+';
    }
}
