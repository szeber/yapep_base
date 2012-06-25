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
 *
 * @package      YapepBase
 * @subpackage   Response
 */
interface IOutput {

	/**
	 * Outputs all parameters.
	 *
	 * @return void
	 */
	public function out();
}