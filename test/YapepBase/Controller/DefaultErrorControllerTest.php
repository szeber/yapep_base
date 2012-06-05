<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Test\Controller
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Controller;

use YapepBase\Controller\DefaultErrorController;
use YapepBase\Request\HttpRequest;
use YapepBase\Response\HttpResponse;
use YapepBase\Mock\Response\OutputMock;
/**
 * Test for the default error controller
 *
 * @package    YapepBase
 * @subpackage Test\Controller
 */
class DefaultErrorControllerTest extends \PHPUnit_Framework_TestCase {

	public function testErrors() {
		$response = null;

		// 404 handling
		$controller = $this->getController($response);
		$controller->run(404);

		$this->assertContains('Page not found', $response->getRenderedBody(), 'Invalid output from action');
		$this->assertSame(404, $response->getStatusCode(), 'Invalid status code after action has run');

		// 500 handling
		$controller = $this->getController($response);
		$controller->run(500);

		$this->assertContains('Internal server error', $response->getRenderedBody(), 'Invalid output from action');
		$this->assertSame(500, $response->getStatusCode(), 'Invalid status code after action has run');
	}

	/**
	 * Instantiates a new controller
	 *
	 * @param \YapepBase\Response\HttpResponse $response   The response object. (outgoing param)
	 *
	 * @return \YapepBase\Controller\DefaultErrorController
	 */
	public function getController(&$response = null) {
		$request    = new HttpRequest(array(), array(), array(), array('REQUEST_URI' => '/'), array(), array(), false);
		$output     = new OutputMock();
		$response   = new HttpResponse($output);
		return new DefaultErrorController($request, $response);
	}
}