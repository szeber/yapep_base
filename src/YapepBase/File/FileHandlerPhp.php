<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage File
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\File;


use YapepBase\Exception\File\Exception;
use YapepBase\Exception\File\NotFoundException;
use YapepBase\Exception\ParameterException;

/**
 * Simple wrapper class for basic filesystem manipulations via PHP function.
 *
 * @package    YapepBase
 * @subpackage File
 */
class FileHandlerPhp implements IFileHandler {

	/**
	 * Sets the access and modification time of a file or directory.
	 *
	 * @link http://php.net/manual/en/function.touch.php
	 *
	 * @param string $path               Tha path to the file or directory.
	 * @param int    $modificationTime   The modification time to set (timestamp).
	 * @param int    $accessTime         The access time to set(timestamp).
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\File\Exception   If the operation failed.
	 */
	public function touch($path, $modificationTime = null, $accessTime = null) {
		if (!touch($path, $modificationTime, $accessTime)) {
			throw new Exception('Touch failed for path: ' . $path);
		}
	}

	/**
	 * Makes a directory. Be aware that by default it is recursive.
	 *
	 * @link http://php.net/manual/en/function.mkdir.php
	 *
	 * @param string $path          The directory or structure(in case of recursive mode) to create.
	 * @param int    $mode          The mode (rights) of the created directory, use octal value.
	 * @param bool   $isRecursive   If TRUE the whole structure will be created.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\File\Exception   If the operation failed.
	 */
	public function makeDirectory($path, $mode = 0755, $isRecursive = true) {
		if (!mkdir($path, $mode, $isRecursive)) {
			throw new Exception('Failed to create directory: ' . $path);
		}

		// Since mkdir() is affected by the umask, do a chmod, so the new directory will have the correct permissions
		$this->changeMode($path, $mode);
	}

	/**
	 * Writes the given content to a file.
	 *
	 * @link http://php.net/manual/en/function.file-put-contents.php
	 *
	 * @param string $path     Path to the file.
	 * @param mixed  $data     Can be either a string, an array or a stream resource.
	 * @param bool   $append   If TRUE, the given data will appended after the already existent data.
	 * @param bool   $lock     If TRUE, the file will be locked at the writing.
	 *
	 * @return int   The byte count of the written data.
	 *
	 * @throws \YapepBase\Exception\File\Exception   If the operation failed.
	 */
	public function write($path, $data, $append = false, $lock = false) {
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

		return $result;
	}

	/**
	 * Changes the owner group and user of the file.
	 *
	 * @link http://php.net/manual/en/function.chgrp.php
	 * @link http://php.net/manual/en/function.chown.php
	 *
	 * @param string     $path    Path to the file or directory.
	 * @param string|int $group   Name of the group, or the identifier.
	 * @param string|int $user    Name of the user, or the identifier.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\File\NotFoundException   If the file was not found.
	 * @throws \YapepBase\Exception\File\Exception   If it failed to set the owner.
	 */
	public function changeOwner($path, $group = null, $user = null) {
		if (!$this->checkIsPathExists($path)) {
			throw new NotFoundException($path, 'Resource not found while changing owner: ' . $path);
		}
		if (!is_null($group) && !chgrp($path, $group)) {
			throw new Exception('Failed to set the group "' . $group . '" of the resource: ' . $path);
		}
		if (!is_null($user) && !chown($path, $user)) {
			throw new Exception('Failed to set the user "' . $user . '" of the resource: ' . $path);
		}
	}

	/**
	 * Changes the mode of the file.
	 *
	 * @link http://php.net/manual/en/function.chmod.php
	 *
	 * @param string $path  Path to the file or directory.
	 * @param int    $mode  The mode to set, use octal values.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\File\NotFoundException   If the file was not found.
	 * @throws \YapepBase\Exception\File\Exception   If it failed to set the mode.
	 */
	public function changeMode($path, $mode) {
		if (!$this->checkIsPathExists($path)) {
			throw new NotFoundException($path, 'Resource not found while changing mode: ' . $path);
		}
		if (!chmod($path, $mode)) {
			throw new Exception('Failed to set the mode "' . decoct($mode) . '" of the resource: ' . $path);
		}
	}

	/**
	 * Copies a file.
	 *
	 * If the destination path already exists, it will be overwritten.
	 *
	 * @link http://php.net/manual/en/function.copy.php
	 *
	 * @param string $source        Path to the source file.
	 * @param string $destination   The destination path.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\File\NotFoundException   If the source file was not found.
	 * @throws \YapepBase\Exception\File\Exception   If it failed to set the mode.
	 */
	public function copy($source, $destination) {
		if (!$this->checkIsPathExists($source)) {
			throw new NotFoundException($source, 'Source file not found for copy: ' . $source);
		}
		if (!copy($source, $destination)) {
			throw new Exception('Failed to copy file from ' . $source . ' to ' . $destination);
		}
	}

	/**
	 * Deletes a file.
	 *
	 * @link http://php.net/manual/en/function.unlink.php
	 *
	 * @param string $path   Path to the file.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\File\NotFoundException   If the given path is not found.
	 * @throws \YapepBase\Exception\File\Exception           If it failed to delete the file or the given path
	 *                                                       is not a regular file.
	 */
	public function remove($path) {
		if (!$this->checkIsFile($path)) {
			throw new Exception('The given path is not a valid file: ' . $path);
		}

		if (!unlink($path)) {
			throw new Exception('Failed to remove file: ' . $path);
		}
	}

	/**
	 * Deletes a directory.
	 *
	 * @param string $path          The directory to delete.
	 * @param bool   $isRecursive   If TRUE the contents will be removed too.
	 *
	 * @throws \YapepBase\Exception\File\Exception   If the given path is not empty, and recursive mode is off.
	 *                                               Or if the given path is not a valid directory, or deletion failed.
	 *
	 * @return void
	 */
	public function removeDirectory($path, $isRecursive = false) {
		if (!$this->checkIsPathExists($path)) {
			return;
		}
		if (!$this->checkIsDirectory($path)) {
			throw new Exception('The given path is not a directory: ' . $path);
		}

		$content = $this->getList($path);
		// The given directory is not empty, but the deletion is not recursive
		if (!$isRecursive && !empty($content)) {
			throw new Exception('The given directory is not empty: ' . $path);
		}

		// Remove the contents one-by-one
		foreach ($content as $subPath) {
			$fullPath = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $subPath;

			// We found a directory
			if ($this->checkIsDirectory($fullPath)) {
				$this->removeDirectory($fullPath, true);
			}
			// We found a file
			else {
				$this->remove($fullPath);
			}
		}

		if (!rmdir($path)) {
			throw new Exception('Failed to delete directory: ' . $path);
		}
	}

	/**
	 * Moves the given file to the given destination.
	 *
	 * @link http://php.net/manual/en/function.rename.php
	 * @link http://php.net/manual/en/function.move-uploaded-file.php
	 *
	 * @param string $sourcePath          Path of the file to move.
	 * @param string $destinationPath     Destination of the moved file.
	 * @param bool   $checkIfIsUploaded   If TRUE it will move the file only if the file was uploaded through HTTP.
	 *
	 * @throws \YapepBase\Exception\File\NotFoundException   If the source file is not found.
	 * @throws \YapepBase\Exception\File\Exception   If the source file is not uploaded through HTTP and its checked
	 *                                               or the move failed.
	 *
	 * @return void
	 */
	public function move($sourcePath, $destinationPath, $checkIfIsUploaded = false) {
		if (!$this->checkIsPathExists($sourcePath)) {
			throw new NotFoundException($sourcePath, 'The source file is not found for a file move: ' . $sourcePath);
		}
		if ($checkIfIsUploaded && !is_uploaded_file($sourcePath)) {
			throw new Exception('The given file is not uploaded through HTTP: ' . $sourcePath);
		}
		if (!rename($sourcePath, $destinationPath)) {
			throw new Exception('Failed to move file from ' . $sourcePath . ' to ' . $destinationPath);
		}
	}

	/**
	 * Returns the parent directory's path.
	 *
	 * @link http://php.net/manual/en/function.dirname.php
	 *
	 * @param string $path   Path of the file or directory.
	 *
	 * @return string
	 */
	public function getParentDirectory($path) {
		return dirname($path);
	}

	/**
	 * Returns the current working directory.
	 *
	 * @return string|bool   The full path of the current directory or FALSE on failure.
	 */
	public function getCurrentDirectory() {
		return getcwd();
	}

	/**
	 * Reads entire file into a string.
	 *
	 * @link http://php.net/manual/en/function.file-get-contents.php
	 *
	 * @param string $path        Path to the file to open
	 * @param int    $offset      Offset where the reading starts on the original stream.
	 * @param int    $maxLength   Maximum length of data read.
	 *
	 * @throws \YapepBase\Exception\File\NotFoundException   If the given path does not exist.
	 * @throws \YapepBase\Exception\File\Exception   If the given path is not a file or the read failed.
	 * @throws \YapepBase\Exception\ParameterException   If the given maxLength is less then 0.
	 *
	 * @return string   The content of the file, or FALSE on failure.
	 */
	public function getAsString($path, $offset = -1, $maxLength = null) {
		if (!$this->checkIsFile($path)) {
			throw new Exception('The given path is not a file: ' . $path);
		}
		if (!is_null($maxLength) && $maxLength < 0) {
			throw new ParameterException('The maximum length cannot be less then 0. ' . $maxLength . ' given');
		}

		// The file_get_contents's maxlen parameter does not have a default value
		if (is_null($maxLength)) {
			$result = file_get_contents($path, null, null, $offset);
		}
		else {
			$result = file_get_contents($path, null, null, $offset, $maxLength);
		}

		if (false === $result) {
			throw new Exception('Failed to read file: ' . $path);
		}

		return $result;
	}

	/**
	 * Returns the list of files and directories at the given path.
	 *
	 * @param string $path   The directory that will be scanned.
	 *
	 * @throws \YapepBase\Exception\File\NotFoundException   If the given path does not exist.
	 * @throws \YapepBase\Exception\File\Exception   If the given path is not a directory.
	 *
	 * @return array
	 */
	public function getList($path) {
		if (!$this->checkIsDirectory($path)) {
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

	/**
	 * Lists the content of the given directory by glob.
	 *
	 * @link http://php.net/manual/en/function.glob.php
	 *
	 * @param string $path      The path where the function should list.
	 * @param string $pattern   The pattern to match.
	 * @param int    $flags     Flags to modify the behaviour of the search {@uses GLOB_*}.
	 *
	 * @throws \YapepBase\Exception\File\NotFoundException   If the given path does not exist.
	 * @throws \YapepBase\Exception\File\Exception   If the given path is not a directory.
	 *
	 * @return array|bool    The found path names, or FALSE on failure.
	 */
	public function getListByGlob($path, $pattern, $flags = null) {
		if (!$this->checkIsDirectory($path)) {
			throw new Exception('The given path is not a valid directory: ' . $path);
		}

		$currentDir = $this->getCurrentDirectory();
		chdir($path);
		$result = glob($pattern, $flags);
		chdir($currentDir);

		return $result;
	}

	/**
	 * Returns the file modification time.
	 *
	 * @link http://php.net/manual/en/function.filemtime.php
	 *
	 * @param string $path   Path to the file or directory.
	 *
	 * @throws \YapepBase\Exception\File\NotFoundException   If the given path does not exist.
	 * @throws \YapepBase\Exception\File\Exception   If we failed to get the modification time.
	 *
	 * @return int   A unix timestamp, or FALSE on failure.
	 */
	public function getModificationTime($path) {
		if (!$this->checkIsPathExists($path)) {
			throw new NotFoundException($path, 'The given path does not exist: ' . $path);
		}
		$result = filemtime($path);

		if (false === $result) {
			throw new Exception('Failed to get modification time for file: ' . $path);
		}

		return $result;
	}

	/**
	 * Returns the size of the file.
	 *
	 * @link http://php.net/manual/en/function.filesize.php
	 *
	 * @param string $path   Path to the file.
	 *
	 * @throws \YapepBase\Exception\File\NotFoundException   If the given path does not exist.
	 * @throws \YapepBase\Exception\File\Exception   If we failed to get the file size.
	 *
	 * @return int|bool   The size of the file in bytes, or FALSE on failure.
	 */
	public function getSize($path) {
		if (!$this->checkIsPathExists($path)) {
			throw new NotFoundException($path, 'The given path does not exist: ' . $path);
		}
		$result =  filesize($path);

		if (false === $result) {
			throw new Exception('Failed to get the size of file: ' . $path);
		}

		return $result;
	}

	/**
	 * Checks if the given directory or file exists.
	 *
	 * @link http://php.net/manual/en/function.file-exists.php
	 *
	 * @param string $path   Path to the file or directory.
	 *
	 * @return bool   TRUE if it exits, FALSE if not.
	 */
	public function checkIsPathExists($path) {
		return file_exists($path);
	}

	/**
	 * Checks if the given path is a directory or not.
	 *
	 * @link http://php.net/manual/en/function.is-dir.php
	 *
	 * @param string $path   The path to check.
	 *
	 * @return bool   TRUE if it is a directory, FALSE if not.
	 *
	 * @throws \YapepBase\Exception\File\NotFoundException   If the path does not exist.
	 */
	public function checkIsDirectory($path) {
		if (!$this->checkIsPathExists($path)) {
			throw new NotFoundException($path, 'The given path does not exist: ' . $path);
		}
		return is_dir($path);
	}

	/**
	 * Checks if the given path is a file or not.
	 *
	 * @link http://php.net/manual/en/function.is-file.php
	 *
	 * @param string $path   The path to check.
	 *
	 * @return bool   TRUE if it is a file, FALSE if not.
	 *
	 * @throws \YapepBase\Exception\File\NotFoundException   If the path does not exits
	 */
	public function checkIsFile($path) {
		if (!$this->checkIsPathExists($path)) {
			throw new NotFoundException($path, 'The given path does not exist: ' . $path);
		}
		return is_file($path);
	}

	/**
	 * Checks if the given path is a symbolic link or not.
	 *
	 * @link http://php.net/manual/en/function.is-link.php
	 *
	 * @param string $path   The path to check.
	 *
	 * @return bool   TRUE if it is a symlink, FALSE if not.
	 *
	 * @throws \YapepBase\Exception\File\NotFoundException   If the path does not exits
	 */
	public function checkIsSymlink($path) {
		if (!$this->checkIsPathExists($path)) {
			throw new NotFoundException($path, 'The given path does not exist: ' . $path);
		}
		return is_link($path);
	}

	/**
	 * Checks if the given path is readable.
	 *
	 * @link http://php.net/manual/en/function.is-writable.php
	 *
	 * @param string $path   The path to check.
	 *
	 * @return bool   TRUE if it is readable, FALSE if not.
	 *
	 * @throws \YapepBase\Exception\File\NotFoundException   If the path does not exits
	 */
	public function checkIsReadable($path) {
		if (!$this->checkIsPathExists($path)) {
			throw new NotFoundException($path, 'The given path does not exist: ' . $path);
		}
		return is_readable($path);
	}

	/**
	 * Checks if the given path is writable.
	 *
	 * @link http://php.net/manual/en/function.is-writable.php
	 *
	 * @param string $path   The path to check.
	 *
	 * @return bool   TRUE if it is writable, FALSE if not.
	 *
	 * @throws \YapepBase\Exception\File\NotFoundException   If the path does not exits
	 */
	public function checkIsWritable($path) {
		if (!$this->checkIsPathExists($path)) {
			throw new NotFoundException($path, 'The given path does not exist: ' . $path);
		}
		return is_writable($path);
	}

	/**
	 * Returns trailing name component of path
	 *
	 * @link http://php.net/manual/en/function.basename.php
	 *
	 * @param string $path     The path.
	 * @param string $suffix   If the name component ends in suffix this will also be cut off.
	 *
	 * @return string
	 */
	public function getBaseName($path, $suffix = null) {
		return basename($path, $suffix);
	}
}