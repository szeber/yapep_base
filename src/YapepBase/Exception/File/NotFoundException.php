<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package    YapepBase
 * @subpackage Exception\File
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Exception\File;


/**
 * Exception that is thrown for file not found errors.
 *
 * @package    YapepBase
 * @subpackage Exception\File
 */
class NotFoundException extends Exception {

	/**
	 * The path and name of the file that was not found.
	 *
	 * @var string
	 */
	protected $fileName;

	/**
	 * Constructor.
	 *
	 * @param string     $fileName   Name and path of the file that was not found.
	 * @param string     $message    The message for the exception.
	 * @param int        $code       Code for the exception.
	 * @param \Exception $previous   The previous exception.
	 * @param mixed      $data       Any debugging data.
	 */
	public function __construct($fileName, $message = "", $code = 0, \Exception $previous = null, $data = null) {
		parent::__construct($message, $code, $previous, $data);

		$this->fileName = $fileName;
	}

	/**
	 * Returns the name and path of the file that was not found.
	 *
	 * @return string
	 */
	public function getFileName() {
		return $this->fileName;
	}
}
