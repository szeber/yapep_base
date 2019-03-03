<?php
declare(strict_types = 1);

namespace YapepBase\Router\DataObject\Param;

use YapepBase\Exception\InvalidArgumentException;

class Regex extends ParamAbstract
{
    /** @var string */
    protected $pattern;

    public function __construct(array $paramData)
    {
        parent::__construct($paramData);

        if (!isset($paramData['pattern'])) {
            throw new InvalidArgumentException('No "pattern" value for regex parameter');
        }

        $this->pattern = (string)$paramData['pattern'];
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }
}
