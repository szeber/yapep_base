<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Exception
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Exception;

/**
 * Exception class
 *
 * @package    YapepBase
 * @subpackage Exception
 */
class Exception extends \Exception {

	/**
	 * Any debugging data.
	 *
	 * @var mixed
	 */
	protected $data;

	/**
	 * Constructor.
	 *
	 * @param string     $message    The exception message.
	 * @param int        $code       The exception code.
	 * @param \Exception $previous   Previous exceptions.
	 * @param mixed      $data       Any debugging data.
	 */
	public function __construct($message = "", $code = 0, \Exception $previous = null, $data = null) {
		parent::__construct($message, $code, $previous);
		$this->data = $data;
	}

	/**
	 * Returns the debugging data if set.
	 *
	 * @return mixed
	 */
	public function getData() {
		return $this->data;
	}

}