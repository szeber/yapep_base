<?php
declare(strict_types=1);

namespace YapepBase\Router\Entity\Param;

class Uuid extends ParamAbstract
{
    public function getPattern(): string
    {
        return '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}';
    }

    protected function getExtraArrayFields(): array
    {
        return [];
    }
}
