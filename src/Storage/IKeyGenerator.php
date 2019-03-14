<?php
declare(strict_types = 1);

namespace YapepBase\Storage;

interface IKeyGenerator
{
    /**
     * @param bool   $hashing If TRUE the generated key will be hashed.
     * @param string $prefix  Prefix of the generated key
     * @param string $suffix  Suffix of the generated key
     */
    public function __construct(bool $hashing, string $prefix = '', string $suffix = '');

    /**
     * Generates the full key.
     */
    public function generate(string $key): string;

    /**
     * Returns TRUE if the generator hashes the keys.
     */
    public function isHashing(): bool;

    /**
     * Sets if the generator should hash the keys
     *
     * @return static
     */
    public function setHashing(bool $hash);

    /**
     * Returns the key prefix
     */
    public function getPrefix(): string;

    /**
     * Sets the key prefix.
     *
     * @return static
     */
    public function setPrefix(string $prefix);

    /**
     * Returns the key suffix
     */
    public function getSuffix(): string;

    /**
     * Sets the key suffix.
     */
    public function setSuffix(string $suffix);
}
