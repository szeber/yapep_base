<?php
declare(strict_types = 1);

namespace YapepBase\Router\DataObject\Param;

use YapepBase\Exception\InvalidArgumentException;

abstract class ParamAbstract implements IParam
{
    /** @var string */
    protected $name;

    public function __construct(array $paramData)
    {
        if (!isset($paramData['name']) || strlen($paramData['name']) == 0) {
            throw new InvalidArgumentException('No name set for the param');
        }

        $this->name = (string)$paramData['name'];
    }

    public function getName(): string
    {
        return $this->name;
    }
}
