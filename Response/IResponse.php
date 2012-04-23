<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Response
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Response;

use YapepBase\View\ViewAbstract;

/**
 * Response interface
 *
 * @package    YapepBase
 * @subpackage Response
 */
interface IResponse {

	/**
	 * Constructor to set the output. If no output is given, it should use
	 * whatever is default for the response.
	 *
	 * @param \YapepBase\Response\IOutput $output The output handler to use.
	 */
	public function __construct(IOutput $output = null);

	/**
	 * Sets the response body.
	 *
	 * @param \YapepBase\View\ViewAbstract $body
	 */
	public function setBody(ViewAbstract $body);

	/**
	 * Sets the already rendered body.
	 *
	 * @param string $body
	 */
	public function setRenderedBody($body);

	/**
	 * Sends the response
	 *
	 * @throws \YapepBase\Exception\Exception   If called after sendError()
	 */
	public function send();

	/**
	 * Instantly outputs an error message.
	 *
	 * This method is used to signal a fatal error to the client.
	 *
	 * @throws \YapepBase\Exception\Exception   If called after send()
	 */
	public function sendError();
}