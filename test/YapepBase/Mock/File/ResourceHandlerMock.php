<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Mock\File
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Mock\File;


use YapepBase\File\ResourceHandlerAbstract;

/**
 * Mock class for the ResourceHandlerAbstract
 *
 * @package    YapepBase
 * @subpackage Mock\File
 */
class ResourceHandlerMock extends ResourceHandlerAbstract {

	/**
	 * Path of the opened file.
	 *
	 * @var string
	 */
	public $path;

	/**
	 * Access type used to open the file.
	 *
	 * @var int
	 */
	public $accessType;

	/**
	 * Is the file opened in binary mode.
	 *
	 * @var bool
	 */
	public $isBinary;

	/**
	 * Opens a file or a URL.
	 *
	 * Be aware as on error this will raise an E_WARNING.
	 *
	 * @param string $path         Path to the file to open
	 * @param int    $accessType   How to open the file. Bitmask created from the {@uses self::ACCESS_TYPE_*} constants.
	 * @param bool   $isBinary     If set to TRUE the file will be opened in binary mode.
	 *
	 * @return void
	 */
	protected function openResource($path, $accessType, $isBinary = true) {
		$this->path       = $path;
		$this->accessType = $accessType;
		$this->isBinary   = $isBinary;
	}

	/**
	 * Closes the given resource.
	 *
	 * @return bool   TRUE on success, FALSE on error.
	 */
	public function closeResource() {
		return true;
	}

	/**
	 * Checks if the pointer is at the end of the file.
	 *
	 * @throws \YapepBase\Exception\File\Exception   In case the object does not have an opened file.
	 *
	 * @return bool   TRUE if the pointer is at the end, FALSE otherwise.
	 */
	public function checkIfPointerIsAtTheEnd() {
		return false;
	}

	/**
	 * Gets a character from the given resource.
	 *
	 * @throws \YapepBase\Exception\File\Exception   In case the object does not have an opened file.
	 *
	 * @return string|bool   The character, or FALSE if the pointer is at the end of the resource.
	 */
	public function getCharacter() {
		return 'a';
	}

	/**
	 * Gets a line from the given resource.
	 *
	 * @link http://php.net/manual/en/function.fgets.php
	 *
	 * @param int $length   If set the reading will end when the given length reached.
	 *
	 * @throws \YapepBase\Exception\File\Exception   In case the object does not have an opened file.
	 *
	 * @return string|bool   The line. Or FALSE if the pointer is at the end of the resource, or on error.
	 */
	public function getLine($length = null) {
		return 'aa';
	}
}