<?php
declare(strict_types=1);

namespace YapepBase\View\Data;

use YapepBase\Exception\InvalidArgumentException;

/**
 * Provides an interface for a data storage what can escape the returned data
 */
interface ICanEscape
{
    /**
     * Sets the given value with the given key to the object.
     *
     * @throws InvalidArgumentException   If the given key already exists.
     */
    public function set(string $key, $value): void;

    /**
     * Sets the elements of the given array by their key
     *
     * @throws InvalidArgumentException    If any of the keys already exist.
     */
    public function setMass(array $valuesByName): void;

    /**
     * Returns the value escaped for HTML
     */
    public function getForHtml(string $key);

    /**
     * Returns the value escaped for Javascript
     */
    public function getForJavascript(string $key);

    /**
     * Returns the value escaped for HTML
     *
     * !!! Warning the result of this method is unescaped which poses threat on security and reliability as well !!!
     */
    public function getRaw(string $key);

    /**
     * Checks if the given key exists
     */
    public function has(string $key): bool;

    /**
     * Clears the stored data
     */
    public function clear(): void;
}
