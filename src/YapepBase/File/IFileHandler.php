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


/**
 * Interface for basic file and directory handler methods.
 *
 * @package    YapepBase
 * @subpackage File
 */
interface IFileHandler {

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
	public function touch($path, $modificationTime = null, $accessTime = null);

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
	public function makeDirectory($path, $mode = 0755, $isRecursive = true);

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
	public function write($path, $data, $append = false, $lock = false);

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
	public function changeOwner($path, $group = null, $user = null);

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
	public function changeMode($path, $mode);

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
	public function copy($source, $destination);

	/**
	 * Deletes a file.
	 *
	 * @link http://php.net/manual/en/function.unlink.php
	 *
	 * @param string $path   Path to the file.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\File\NotFoundException   If the given path is not a valid file.
	 * @throws \YapepBase\Exception\File\Exception   If it failed to delete the file.
	 */
	public function remove($path);

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
	public function removeDirectory($path, $isRecursive = false);

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
	public function move($sourcePath, $destinationPath, $checkIfIsUploaded = false);

	/**
	 * Returns the parent directory's path.
	 *
	 * @link http://php.net/manual/en/function.dirname.php
	 *
	 * @param string $path   Path of the file or directory.
	 *
	 * @return string
	 */
	public function getParentDirectory($path);

	/**
	 * Returns the current working directory.
	 *
	 * @return string|bool   The full path of the current directory or FALSE on failure.
	 */
	public function getCurrentDirectory();

	/**
	 * Checks if the given directory or file exists.
	 *
	 * @link http://php.net/manual/en/function.file-exists.php
	 *
	 * @param string $path   Path to the file or directory.
	 *
	 * @return bool   TRUE if it exits, FALSE if not.
	 */
	public function checkIsPathExists($path);

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
	public function getAsString($path, $offset = -1, $maxLength = null);

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
	public function getList($path);

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
	public function getListByGlob($path, $pattern, $flags = null);

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
	public function getModificationTime($path);

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
	public function getSize($path);


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
	public function checkIsDirectory($path);

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
	public function checkIsFile($path);

	/**
	 * Checks if the given path is a symbolic link or not.
	 *
	 * @link http://php.net/manual/en/function.is-link.php
	 *
	 * @param string $path   The path to check.
	 *
	 * @return bool   TRUE if it is a file, FALSE if not.
	 *
	 * @throws \YapepBase\Exception\File\NotFoundException   If the path does not exits
	 */
	public function checkIsSymlink($path);

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
	public function getBaseName($path, $suffix = null);
}