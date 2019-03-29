<?php
declare(strict_types=1);

namespace YapepBase\Router\Entity\Param;

class Alpha extends ParamAbstract
{
    public function getPattern(): string
    {
        return '[a-zA-Z]+';
    }

    protected function getExtraArrayFields(): array
    {
        return [];
    }
}
