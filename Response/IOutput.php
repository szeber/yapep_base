<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Response
 * @author       Janos Pasztor <j.pasztor@ixolit.com>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Response;

/**
 * Classes implementing this interface handle the raw output to the browser,
 * etc. It has been implemented to separate the PHP-dependant code parts.
 */
interface IOutput {

	/**
	 * Outputs all parameters.
	 *
	 * @param string $string1 First string to output
	 * @param string $string2 Second string to output
	 * @param string $stringn n-th string to output
	 */
	public function out();
}