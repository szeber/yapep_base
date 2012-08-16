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
 * RedirectException class.
 *
 * Not descendant of YapepBase\Exception\Exception, and it should only be catched by the Application
 * or a controller if neccessary.
 *
 * @package    YapepBase
 * @subpackage Exception
 */
class RedirectException extends \Exception {
	/** Internal redirect type. */
	const TYPE_INTERNAL = 1;
	/** External redirect type. */
	const TYPE_EXTERNAL = 2;

	/**
	 * The target of the redirect.
	 *
	 * @var string
	 */
	protected $target;

	/**
	 * Constructor.
	 *
	 * @param string     $target     The target of the redirect. An URL for external, or a route for internal redirects.
	 * @param int        $type       The type of the redirect. {@uses self::TYPE_*}
	 * @param \Exception $previous   The previous exception, if the redirect is caused by one.
	 *
	 * @return return_type
	 */
	public function __construct($target, $type, \Exception $previous = null) {
		$message = 'Redirecting to: ' . $target;
		$this->target = $target;
		parent::__construct($message, $type, $previous);
	}

	/**
	 * Returns the target of the redirect. (URL or route)
	 *
	 * @return string
	 */
	public function getTarget() {
		return $this->target;
	}

}