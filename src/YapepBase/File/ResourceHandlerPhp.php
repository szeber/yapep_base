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


use SplFileObject;

use YapepBase\File\ResourceHandlerAbstract;
use YapepBase\Exception\File\Exception;
use YapepBase\Exception\ParameterException;

/**
 * Opens a file or a stream and handles the opened resource with PHP functions.
 *
 * @package    YapepBase
 * @subpackage File
 */
class ResourceHandlerPhp extends ResourceHandlerAbstract {

	/**
	 * Open the file for reading only.
	 * The file pointer will be at the beginning of the file.
	 */
	const MODE_READ_ONLY = 'r';

	/**
	 * Open the file for writing only.
	 * Place the pointer at the beginning of the file.
	 * Truncate the file to zero length.
	 * If the file does not exists, attempt to create it.
	 */
	const MODE_WRITE_ONLY = 'w';

	/**
	 * Open a file for writing only.
	 * Place the pointer at the end of the file.
	 * Do not truncate the file to zero length.
	 * If the file does not exist, attempt to create it.
	 */
	const MODE_APPEND = 'a';

	/**
	 * Open a file for writing only.
	 * Place the pointer at the beginning of the file.
	 * Does not truncate an existent file.
	 * If the file does not exists, attempt to create it.
	 */
	const MODE_WRITE_ONLY_WITHOUT_TRUNCATE = 'c';

	/**
	 * Object to the opened file.
	 *
	 * @var \SplFileObject
	 */
	protected $splFile;

	/**
	 * Opens a file or a URL.
	 *
	 * Be aware as on error this will raise an E_WARNING.
	 *
	 * @param string $path         Path to the file to open
	 * @param int    $accessType   How to open the file. Bitmask created from the {@uses self::ACCESS_TYPE_*} constants.
	 * @param bool   $isBinary     If set to TRUE the file will be opened in binary mode.
	 *
	 * @throws \YapepBase\Exception\ParameterException   If the given accessType is invalid.
	 * @throws \RuntimeException                         If it failed to open the file.
	 *
	 * @return void
	 */
	protected function openResource($path, $accessType, $isBinary = true) {
		$mode = $this->getModeFromAccessType($accessType);
		$mode .= ($isBinary ? 'b' : '');

		$this->splFile = new SplFileObject($path, $mode);
	}

	/**
	 * Closes the given resource.
	 *
	 * @return bool   TRUE on success, FALSE on error.
	 */
	public function closeResource() {
		$this->splFile = null;

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
		$this->checkIfFileOpened();
		return $this->splFile->eof();
	}

	/**
	 * Gets a character from the given resource.
	 *
	 * @throws \YapepBase\Exception\File\Exception   In case the object does not have an opened file.
	 *
	 * @return string|bool   The character, or FALSE if the pointer is at the end of the resource.
	 */
	public function getCharacter() {
		$this->checkIfFileOpened();
		return $this->splFile->fgetc();
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
		$this->checkIfFileOpened();

		if ($this->checkIfPointerIsAtTheEnd()) {
			return false;
		}
		return $this->splFile->fgets();
	}

	/**
	 * Returns the Mode to the given accessType.
	 *
	 * @param int $accessType   The bitmask of the access type.
	 *
	 * @return string|bool   The mode can be used in an fopen() function call.
	 */
	protected function getModeFromAccessType($accessType) {
		$usableModes = array(
			self::MODE_WRITE_ONLY                  => array(
														self::ACCESS_TYPE_WRITE | self::ACCESS_TYPE_TRUNCATE
															| self::ACCESS_TYPE_POINTER_AT_THE_END,
														self::ACCESS_TYPE_WRITE | self::ACCESS_TYPE_TRUNCATE
													),
			self::MODE_APPEND                      => array(
														self::ACCESS_TYPE_WRITE | self::ACCESS_TYPE_POINTER_AT_THE_END
													),
			self::MODE_WRITE_ONLY_WITHOUT_TRUNCATE => array(
														self::ACCESS_TYPE_WRITE
													),
			self::MODE_READ_ONLY                   => array(0),
		);

		foreach ($usableModes as $mode => $possibleAccessTypes) {
			if (in_array($accessType, $possibleAccessTypes)) {
				return $mode;
			}
		}

		throw new ParameterException('Unusable Access Type given: ' . $accessType);
	}

	/**
	 * Checks if we have an opened file ot not.
	 *
	 * @throws \YapepBase\Exception\File\Exception   In case the object does not have an opened file.
	 *
	 * @return void
	 */
	protected function checkIfFileOpened() {
		if (empty($this->splFile)) {
			throw new Exception('No opened resource.');
		}
	}
}