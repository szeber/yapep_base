<?php
declare(strict_types=1);

namespace YapepBase\Helper;

class ArrayHelper extends HelperAbstract
{
    public function getElementAsInt(array $array, string $key, ?int $default = null): ?int
    {
        return isset($array[$key])
            ? (int)$array[$key]
            : $default;
    }

    public function getElementAsString(array $array, string $key, ?string $default = null): ?string
    {
        return isset($array[$key])
            ? (string)$array[$key]
            : $default;
    }

    public function getElementAsFloat(array $array, string $key, ?float $default = null): ?float
    {
        return isset($array[$key])
            ? (float)$array[$key]
            : $default;
    }

    public function getElementAsArray(array $array, string $key, ?array $default = null): ?array
    {
        return isset($array[$key])
            ? (array)$array[$key]
            : $default;
    }

    public function getElementAsBool(array $array, string $key, ?bool $default = null): ?bool
    {
        return isset($array[$key])
            ? (bool)$array[$key]
            : $default;
    }
}
