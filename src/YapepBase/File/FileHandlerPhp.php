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
	 * @return bool   TRUE on success, FALSE on failure.
	 */
	public function touch($path, $modificationTime = null, $accessTime = null) {
		return touch($path, $modificationTime, $accessTime);
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
	 * @return bool   TRUE on success, FALSE on failure.
	 */
	public function makeDirectory($path, $mode = 0755, $isRecursive = true) {
		return mkdir($path, $mode, $isRecursive);
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
	 * @return int|bool   The byte count of the written data, or FALSE on failure.
	 */
	public function write($path, $data, $append = false, $lock = false) {
		$flag = 0;
		if ($append) {
			$flag = $flag | FILE_APPEND;
		}
		if ($lock) {
			$flag = $flag | LOCK_EX;
		}

		return file_put_contents($path, $data, $flag);
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
	 * @throws \YapepBase\Exception\File\Exception   If it failed to set the owner.
	 *
	 * @return void
	 */
	public function changeOwner($path, $group = null, $user = null) {
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
	 * @return bool   TRUE on success, FALSE on failure.
	 */
	public function changeMode($path, $mode) {
		return chmod($path, $mode);
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
	 * @return bool   TRUE on success, FALSE on failure.
	 */
	public function copy($source, $destination) {
		return copy($source, $destination);
	}

	/**
	 * Deletes a file.
	 *
	 * @link http://php.net/manual/en/function.unlink.php
	 *
	 * @param string $path   Path to the file.
	 *
	 * @throws \YapepBase\Exception\File\Exception   If the given path is not a valid file.
	 *
	 * @return bool   TRUE on success, FALSE on failure.
	 */
	public function remove($path) {
		if (!$this->checkIsFile($path)) {
			throw new Exception('The given path is not a valid file: ' . $path);
		}

		if ($this->checkIsPathExists($path)) {
			return unlink($path);
		}
		return true;
	}

	/**
	 * Deletes a directory.
	 *
	 * @param string $path          The directory to delete.
	 * @param bool   $isRecursive   If TRUE the contents will be removed too.
	 *
	 * @throws \YapepBase\Exception\File\Exception   If the given path is not empty, and recursive mode is off.
	 *                                                  Or if the given path is not a valid directory.
	 *
	 * @return bool   TRUE on success, FALSE on failure.
	 */
	public function removeDirectory($path, $isRecursive = false) {
		if (!$this->checkIsPathExists($path)) {
			return true;
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
			if (is_dir($fullPath)) {
				$this->removeDirectory($fullPath, true);
			}
			// We found a file
			else {
				$this->remove($fullPath);
			}
		}

		return rmdir($path);
	}

	/**
	 * Moves the given file to the given destination.
	 *
	 * @link http://php.net/manual/en/function.rename.php
	 *
	 * @param string $sourcePath          Path of the file to move.
	 * @param string $destinationPath     Destination of the moved file.
	 * @param bool   $checkIfIsUploaded   If TRUE it will move the file only if the file was uploaded through HTTP.
	 *
	 * @throws \YapepBase\Exception\File\Exception   If the source file is not uploaded through HTTP and its checked.
	 *
	 * @return bool   TRUE on success, FALSE on failure.
	 */
	public function move($sourcePath, $destinationPath, $checkIfIsUploaded = false) {
		if ($checkIfIsUploaded && !is_uploaded_file($sourcePath)) {
			throw new Exception('The given file is not uploaded through HTTP: ' . $sourcePath);
		}
		return rename($sourcePath, $destinationPath);
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
	 * Reads entire file into a string.
	 *
	 * @link http://php.net/manual/en/function.file-get-contents.php
	 *
	 * @param string $path        Path to the file to open
	 * @param int    $offset      Offset where the reading starts on the original stream.
	 * @param int    $maxLength   Maximum length of data read.
	 *
	 * @throws \YapepBase\Exception\File\Exception       If the given path does not exist, or it is not a file.
	 * @throws \YapepBase\Exception\ParameterException   If the given maxLength is less then 0.
	 *
	 * @return string|bool   The content of the file, or FALSE on failure.
	 */
	public function getAsString($path, $offset = -1, $maxLength = null) {
		if (!$this->checkIsFile($path)) {
			throw new Exception('The given path does not exist, or it is not a file: ' . $path);
		}
		if (!is_null($maxLength) && $maxLength < 0) {
			throw new ParameterException('The maximum length cannot be less then 0. ' . $maxLength . ' given');
		}

		// The file_get_contents's maxlen parameter does not have a default value ... (I love you PHP)
		if (is_null($maxLength)) {
			return file_get_contents($path, null, null, $offset);
		}
		else {
			return file_get_contents($path, null, null, $offset, $maxLength);
		}
	}

	/**
	 * Returns the list of files and directories at the given path.
	 *
	 * @param string $path   The directory that will be scanned.
	 *
	 * @throws \YapepBase\Exception\File\Exception   If the given path does not exist, or it is not a directory.
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
	 * @throws \YapepBase\Exception\File\Exception   If the given path does not exist, or it is not a directory.
	 *
	 * @return array|bool    The found path names, or FALSE on failure.
	 */
	public function getListByGlob($path, $pattern, $flags = null) {
		if (!$this->checkIsDirectory($path)) {
			throw new Exception('The given path does not exist, or it is not a directory: ' . $path);
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
	 * @throws \YapepBase\Exception\File\Exception   If the given path does not exist.
	 *
	 * @return int|bool   A unix timestamp, or FALSE on failure.
	 */
	public function getModificationTime($path) {
		if (!$this->checkIsPathExists($path)) {
			throw new Exception('The given path does not exist: ' . $path);
		}
		return filemtime($path);
	}

	/**
	 * Returns the size of the file.
	 *
	 * @link http://php.net/manual/en/function.filesize.php
	 *
	 * @param string $path   Path to the file.
	 *
	 * @throws \YapepBase\Exception\File\Exception   If the given path does not exist.
	 *
	 * @return int|bool   The size of the file in bytes, or FALSE on failure.
	 */
	public function getSize($path) {
		if (!$this->checkIsPathExists($path)) {
			throw new Exception('The given path does not exist: ' . $path);
		}
		return filesize($path);
	}

	/**
	 * Checks if the given path is a directory or not.
	 *
	 * @param string $path
	 *
	 * @param string $path   The path to check.
	 *
	 * @return bool   TRUE if it is a directory, FALSE if not.
	 *
	 * @throws \YapepBase\Exception\File\Exception   If the path does not exits
	 */
	public function checkIsDirectory($path) {
		if (!$this->checkIsPathExists($path)) {
			throw new Exception('The given path does not exist: ' . $path);
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
	 * @throws \YapepBase\Exception\File\Exception   If the path does not exits
	 */
	public function checkIsFile($path) {
		if (!$this->checkIsPathExists($path)) {
			throw new Exception('The given path does not exist: ' . $path);
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
	 * @return bool   TRUE if it is a file, FALSE if not.
	 */
	public function checkIsSymlink($path) {
		return is_link($path);
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