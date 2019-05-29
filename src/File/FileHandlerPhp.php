<?php
declare(strict_types=1);

namespace YapepBase\File;

use YapepBase\Exception\InvalidArgumentException;
use YapepBase\File\Exception\Exception;
use YapepBase\File\Exception\NotFoundException;

class FileHandlerPhp implements IFileHandler
{
    public function touch(string $path, int $modificationTime = 0, int $accessTime = 0): void
    {
        if (!touch($path, $modificationTime, $accessTime)) {
            throw new Exception('Touch failed for path: ' . $path);
        }
    }

    public function makeDirectory(string $path, int $mode = 0755, bool $isRecursive = true): void
    {
        if (!mkdir($path, $mode, $isRecursive)) {
            throw new Exception('Failed to create directory: ' . $path);
        }

        // Since mkdir() is affected by the umask, do a chmod, so the new directory will have the correct permissions
        $this->changeMode($path, $mode);
    }

    public function write(string $path, string $data, bool $append = false, bool $lock = false): void
    {
        $flag = 0;

        if ($append) {
            $flag = $flag | FILE_APPEND;
        }
        if ($lock) {
            $flag = $flag | LOCK_EX;
        }

        $result = file_put_contents($path, $data, $flag);

        if (false === $result) {
            throw new Exception('Failed to write data to file: ' . $path);
        }
    }

    public function changeOwner(string $path, ?string $group = null, ?string $user = null): void
    {
        $this->requirePathToExist($path);

        if (!is_null($group) && !chgrp($path, $group)) {
            throw new Exception('Failed to set the group "' . $group . '" of the resource: ' . $path);
        }

        if (!is_null($user) && !chown($path, $user)) {
            throw new Exception('Failed to set the user "' . $user . '" of the resource: ' . $path);
        }
    }

    public function changeMode(string $path, int $mode): void
    {
        $this->requirePathToExist($path);

        if (!chmod($path, $mode)) {
            throw new Exception('Failed to set the mode "' . decoct($mode) . '" of the resource: ' . $path);
        }
    }

    public function copy(string $sourcePath, string $destinationPath): void
    {
        $this->requirePathToExist($sourcePath);

        if (!copy($sourcePath, $destinationPath)) {
            throw new Exception('Failed to copy file from ' . $sourcePath . ' to ' . $destinationPath);
        }
    }

    public function remove(string $path): void
    {
        if (!$this->pathExists($path)) {
            return;
        }

        if ($this->isDirectory($path)) {
            throw new Exception('The given path is a directory: ' . $path);
        }

        if (!unlink($path)) {
            throw new Exception('Failed to remove file: ' . $path);
        }
    }

    public function removeDirectory(string $path, bool $isRecursive = false): void
    {
        if (!$this->pathExists($path)) {
            return;
        }

        if (!$this->isDirectory($path)) {
            throw new Exception('The given path is not a directory: ' . $path);
        }

        $content = $this->getList($path);

        if (!$isRecursive && !empty($content)) {
            throw new Exception('The given directory is not empty: ' . $path);
        }

        foreach ($content as $subPath) {
            $fullPath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $subPath;

            if ($this->isDirectory($fullPath)) {
                $this->removeDirectory($fullPath, true);
            }
            else {
                $this->remove($fullPath);
            }
        }

        if (!rmdir($path)) {
            throw new Exception('Failed to delete directory: ' . $path);
        }
    }

    public function move(string $sourcePath, string $destinationPath, bool $checkIfIsUploaded = false): void
    {
        $this->requirePathToExist($sourcePath);

        if ($checkIfIsUploaded && !is_uploaded_file($sourcePath)) {
            throw new Exception('The given file is not uploaded through HTTP: ' . $sourcePath);
        }

        if (!rename($sourcePath, $destinationPath)) {
            throw new Exception('Failed to move file from ' . $sourcePath . ' to ' . $destinationPath);
        }
    }

    public function getParentDirectory(string $path): string
    {
        return dirname($path);
    }

    public function getCurrentDirectory(): ?string
    {
        $currentDirectory = getcwd();

        return empty($currentDirectory) ? null : $currentDirectory;
    }

    public function getAsString(string $path, int $offset = 0, $maxLength = null): string
    {
        if (!is_null($maxLength) && $maxLength < 0) {
            throw new InvalidArgumentException('The maximum length cannot be less then 0. ' . $maxLength . ' given');
        }

        // The file_get_contents's maxlength parameter does not have a default value
        if (is_null($maxLength)) {
            $result = file_get_contents($path, false, null, $offset);
        } else {
            $result = file_get_contents($path, false, null, $offset, $maxLength);
        }

        if ($result === false) {
            throw new Exception('Failed to read file: ' . $path);
        }

        return $result;
    }

    public function getList(string $path): array
    {
        if (!$this->isDirectory($path)) {
            throw new Exception('The given path is not a valid directory: ' . $path);
        }

        $content = scandir($path);

        if (!empty($content[0]) && $content[0] == '.') {
            unset($content[0]);
        }
        if (!empty($content[1]) && $content[1] == '..') {
            unset($content[1]);
        }

        return $content;
    }

    public function getListByGlob(string $path, string $pattern, int $flags = null): array
    {
        if (!$this->isDirectory($path)) {
            throw new Exception('The given path is not a valid directory: ' . $path);
        }

        $currentDir = $this->getCurrentDirectory();
        chdir($path);
        $result = glob($pattern, $flags);
        chdir($currentDir);

        if ($result === false) {
            throw new Exception('Failed to find paths by ' . $pattern . ' in ' . $path);
        }

        return $result;
    }

    public function getModificationTime(string $path): int
    {
        $this->requirePathToExist($path);

        $result = filemtime($path);

        if ($result === false) {
            throw new Exception('Failed to get modification time for file: ' . $path);
        }

        return $result;
    }

    public function getSize(string $path): int
    {
        $this->requirePathToExist($path);

        $result = filesize($path);

        if ($result === false) {
            throw new Exception('Failed to get the size of file: ' . $path);
        }

        return $result;
    }

    public function pathExists(string $path): bool
    {
        return file_exists($path);
    }

    public function isDirectory(string $path): bool
    {
        $this->requirePathToExist($path);

        return is_dir($path);
    }

    public function isFile(string $path): bool
    {
        $this->requirePathToExist($path);

        return is_file($path);
    }

    public function isSymlink(string $path): bool
    {
        $this->requirePathToExist($path);

        return is_link($path);
    }

    public function isReadable(string $path): bool
    {
        $this->requirePathToExist($path);

        return is_readable($path);
    }

    public function isWritable(string $path): bool
    {
        $this->requirePathToExist($path);

        return is_writable($path);
    }

    public function getBaseName(string $path, ?string $suffix = null): string
    {
        return basename($path, $suffix);
    }

    /**
     * @throws NotFoundException
     */
    private function requirePathToExist(string $path)
    {
        if (!$this->pathExists($path)) {
            throw new NotFoundException($path, 'The given path does not exist: ' . $path);
        }
    }
}
