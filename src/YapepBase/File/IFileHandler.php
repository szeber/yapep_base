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
 * Interface what should be implemented by every File wrapper class.
 *
 * @package    YapepBase
 * @subpackage File
 */
interface IFileHandler {

	/**
	 * Sets the access and modification time of a file or directory.
	 *
	 * @param string $path               Tha path to the file or directory.
	 * @param int    $modificationTime   The modification time to set (timestamp).
	 * @param int    $accessTime         The access time to set(timestamp).
	 *
	 * @return bool   TRUE on success, FALSE on failure.
	 */
	public function touch($path, $modificationTime = null, $accessTime = null);

	/**
	 * Makes a directory. Be aware that by default it is recursive.
	 *
	 * @param string $path          The directory or structure(in case of recursive mode) to create.
	 * @param int    $mode          The mode (rights) of the created directory, use octal value.
	 * @param bool   $isRecursive   If TRUE the whole structure will be created.
	 *
	 * @return bool   TRUE on success, FALSE on failure.
	 */
	public function makeDirectory($path, $mode = 0755, $isRecursive = true);

	/**
	 * Writes the given content to a file.
	 *
	 * @link http://php.net/manual/en/function.file-put-contents.php
	 *
	 * @param string $path      Path to the file.
	 * @param mixed  $data      Can be either a string, an array or a stream resource.
	 * @param int    $flags     Flags to modify the behaviour of the method.
	 *
	 * @return int|bool   The byte count of the written data, or FALSE on failure.
	 */
	public function write($path, $data, $flags = 0);

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
	 * @return bool   TRUE on success, FALSE on failure.
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
	 * @return bool   TRUE on success, FALSE on failure.
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
	 * @return bool   TRUE on success, FALSE on failure.
	 */
	public function copy($source, $destination);

	/**
	 * Deletes a file.
	 *
	 * @link http://php.net/manual/en/function.unlink.php
	 *
	 * @param string   $path      Path to the file.
	 * @param resource $context   A valid context resource.
	 *
	 * @return bool   TRUE on success, FALSE on failure.
	 */
	public function remove($path, $context);

	/**
	 * Deletes a directory.
	 *
	 * @param string $path          The directory to delete.
	 * @param bool   $isRecursive   If TRUE the contents will be removed too.
	 *
	 * @return bool   TRUE on success, FALSE on failure.
	 */
	public function removeDirectory($path, $isRecursive);

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
	 * @return bool   TRUE on success, FALSE on failure.
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
	 * @param string $path             Path to the file to open
	 * @param bool   $useIncludePath   If set to TRUE, it will search for the file in the include_path too.
	 * @param int    $offset           Offset where the reading starts on the original stream.
	 * @param int    $maxLength        Maximum length of data read.
	 *
	 * @return string|bool   The content of the file, or FALSE on failure.
	 */
	public function getAsString($path, $useIncludePath = false, $offset = -1, $maxLength = null);

	/**
	 * Returns the list of files and directories at the given path.
	 *
	 * @param string $path   The directory that will be scanned.
	 *
	 * @return array
	 */
	public function getList($path);

	/**
	 * Returns the file modification time.
	 *
	 * @link http://php.net/manual/en/function.filemtime.php
	 *
	 * @param string $path   Path to the file or directory.
	 *
	 * @return int|bool   A unix timestamp, or FALSE on failure.
	 */
	public function getModificationTime($path);

	/**
	 * Returns the size of the file.
	 *
	 * @link http://php.net/manual/en/function.filesize.php
	 *
	 * @param string $path   Path to the file.
	 *
	 * @return int|bool   The size of the file in bytes, or FALSE on failure.
	 */
	public function getSize($path);

	/**
	 * Lists the content of the given directory by glob.
	 *
	 * @link http://php.net/manual/en/function.glob.php
	 *
	 * @param string $path      The path where the function should list.
	 * @param string $pattern   The pattern to match.
	 * @param int    $flags     Flags to modify the behaviour of the search {@uses GLOB_*}.
	 *
	 * @return array|bool    The found path names, or FALSE on failure.
	 */
	public function getListByGlob($path, $pattern, $flags);

	/**
	 * Checks if the given path is a directory or not.
	 *
	 * @link http://php.net/manual/en/function.is-dir.php
	 *
	 * @param string $path   The path to check.
	 *
	 * @return bool   TRUE if it is a directory, FALSE if not.
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
	 */
	public function checkIsFile($path);

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