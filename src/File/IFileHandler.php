<?php
declare(strict_types=1);

namespace YapepBase\File;

use YapepBase\Exception\InvalidArgumentException;
use YapepBase\File\Exception\Exception;
use YapepBase\File\Exception\NotFoundException;

/**
 * Interface for basic file and directory handler methods.
 */
interface IFileHandler
{
    /**
     * Sets the access and modification time of a file or directory.
     *
     * @throws Exception
     */
    public function touch(string $path, int $modificationTime = 0, int $accessTime = 0): void;

    /**
     * Makes a directory. Be aware that by default it is recursive.
     *
     * @throws Exception
     */
    public function makeDirectory(string $path, int $mode = 0755, bool $isRecursive = true): void;

    /**
     * Writes the given content to a file.
     *
     * @throws Exception
     */
    public function write(string $path, string $data, bool $append = false, bool $lock = false): void;

    /**
     * Changes the owner group and user of the file.
     *
     * @throws NotFoundException
     * @throws Exception
     */
    public function changeOwner(string $path, ?string $group = null, ?string $user = null): void;

    /**
     * Changes the mode of the file.
     *
     * @throws NotFoundException
     * @throws Exception
     */
    public function changeMode(string $path, int $mode): void;

    /**
     * Copies a file.
     *
     * If the destination path already exists, it will be overwritten.
     *
     * @throws NotFoundException
     * @throws Exception
     */
    public function copy(string $sourcePath, string $destinationPath): void;

    /**
     * Deletes a file.
     *
     * @throws NotFoundException
     * @throws Exception
     */
    public function remove(string $path): void;

    /**
     * Deletes a directory.
     *
     * @throws Exception
     */
    public function removeDirectory(string $path, bool $isRecursive = false): void;

    /**
     * Moves the given file to the given destination.
     *
     * @throws NotFoundException
     * @throws Exception
     */
    public function move(string $sourcePath, string $destinationPath, bool $checkIfIsUploaded = false): void;

    /**
     * Returns the parent directory's path.
     */
    public function getParentDirectory(string $path): string;

    /**
     * Returns the current working directory.
     */
    public function getCurrentDirectory(): ?string;

    /**
     * Reads entire file into a string.
     *
     * @throws NotFoundException
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function getAsString(string $path, int $offset = 0, $maxLength = null): string;

    /**
     * Returns the list of files and directories at the given path.
     *
     * @throws Exception
     * @throws NotFoundException
     */
    public function getList(string $path): array;

    /**
     * Lists the content of the given directory by glob.
     *
     * @throws NotFoundException
     * @throws Exception
     */
    public function getListByGlob(string $path, string $pattern, int $flags = null): array;

    /**
     * Returns the file modification time.
     *
     * @throws NotFoundException
     * @throws Exception
     */
    public function getModificationTime(string $path): int;

    /**
     * Returns the size of the file.
     *
     * @throws NotFoundException
     * @throws Exception
     */
    public function getSize(string $path): int;

    /**
     * Checks if the given directory or file exists.
     */
    public function pathExists(string $path): bool;

    /**
     * Checks if the given path is a directory or not.
     *
     * @throws NotFoundException
     */
    public function isDirectory(string $path): bool;

    /**
     * Checks if the given path is a file or not.
     *
     * @throws NotFoundException
     */
    public function isFile(string $path): bool;

    /**
     * Checks if the given path is a symbolic link or not.
     *
     * @throws NotFoundException
     */
    public function isSymlink(string $path): bool;

    /**
     * Checks if the given path is readable.
     *
     * @throws NotFoundException
     */
    public function isReadable(string $path): bool;

    /**
     * Checks if the given path is writable.
     *
     * @throws NotFoundException
     */
    public function isWritable(string $path): bool;

    /**
     * Returns trailing name component of path
     */
    public function getBaseName(string $path, ?string $suffix = null): string;
}
