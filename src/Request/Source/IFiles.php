<?php
declare(strict_types=1);

namespace YapepBase\Request\Source;

interface IFiles
{
    /**
     * Returns TRUE if there were more than 1 file uploaded with the same name. FALSE otherwise
     */
    public function isMultiUpload(string $name): bool;

    /**
     * Returns the file by the given name
     */
    public function get(string $name, int $index = 0): ?File;

    /**
     * Returns TRUE if a file exists with the given name. FALSE otherwise
     */
    public function has(string $name): bool;

    /**
     * Returns all uploaded files
     *
     * @return File[][]
     */
    public function toArray(): array;
}
