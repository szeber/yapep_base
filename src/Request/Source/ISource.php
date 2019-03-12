<?php
declare(strict_types = 1);

namespace YapepBase\Request\Source;

interface ISource
{
    /**
     * Returns the original data as an associative array.
     */
    public function toArray(): array;

    /**
     * Checks if a parameter exists with the given name
     */
    public function has(string $name): bool;

    /**
     * Returns the given parameter as an int if available, returns given default value otherwise
     */
    public function getAsInt(string $name, ?int $default = null): ? int;

    /**
     * Returns the given parameter as a string if available, returns given default value otherwise
     */
    public function getAsString(string $name, ?string $default = null): ? string;

    /**
     * Returns the given parameter as a float if available, returns given default value otherwise
     */
    public function getAsFloat(string $name, ?float $default = null): ? float;

    /**
     * Returns the given parameter as an array if available, returns given default value otherwise
     */
    public function getAsArray(string $name, ?array $default = []): ? array;

    /**
     * Returns the given parameter as a bool if available, returns given default value otherwise
     */
    public function getAsBool(string $name, ?bool $default = null): ? bool;
}
