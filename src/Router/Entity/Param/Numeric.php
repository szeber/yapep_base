<?php
declare(strict_types=1);

namespace YapepBase\Router\Entity\Param;

class Numeric extends ParamAbstract
{
    public function getPattern(): string
    {
        return '[0-9]+';
    }
}
