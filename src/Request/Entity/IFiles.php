<?php
declare(strict_types=1);

namespace YapepBase\Request\Entity;

interface IFiles
{
    /**
     * Returns TRUE if there were more than 1 file uploaded. FALSE otherwise
     */
    public function isMultiUpload(): bool;

    /**
     * Returns the file by the given name
     */
    public function get(string $name): ?UploadedFile;

    /**
     * Returns TRUE if a file exists with the given name. FALSE otherwise
     */
    public function has(string $name): bool;

    /**
     * Returns all uploaded files
     *
     * @return UploadedFile[]
     */
    public function toArray(): array;
}
