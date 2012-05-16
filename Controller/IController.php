<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Controller
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Controller;
use YapepBase\Response\IResponse;
use YapepBase\Request\IRequest;

/**
 * Controller interface
 *
 * @package    YapepBase
 * @subpackage Controller
 */
interface IController {

	/**
	 * Constructor
	 *
	 * @param \YapepBase\Request\IRequest   $request    The request object
	 * @param \YapepBase\Response\IResponse $response   The response object
	 *
	 * @throws \YapepBase\Exception\ControllerException   On error. (eg. incompatible request or response object)
	 */
	public function __construct(IRequest $request, IResponse $response);

	/**
	 * Runs the specified action
	 *
	 * @param string $action   The name of the action (without the controller specific prefix)
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\ControllerException   On controller specific error. (eg. action not found)
	 * @throws \YapepBase\Exception\RedirectException     On redirects. These should not be treated as errors!
	 * @throws \YapepBase\Exception\Exception             On framework related errors.
	 * @throws \Exception                                 On non-framework related errors.
	 */
	public function run($action);
}