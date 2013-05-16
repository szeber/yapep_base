<?php

namespace YapepBase\Mock\Response;

/**
 * @codeCoverageIgnore
 */
class ResponseMock implements \YapepBase\Response\IResponse {
	public function __construct(\YapepBase\Response\IOutput $output = null) {

	}
	public function setBody(\YapepBase\View\ViewAbstract $body) {

	}
	public function setRenderedBody($body) {

	}
	public function send() {

	}

	/**
	 * Renders the output.
	 *
	 * @return void
	 */
	public function render() {
	}

	public function sendError() {

	}

	/**
	 * Clears all previous, not sent output in the buffer.
	 *
	 * @return void
	 */
	public function clearAllOutput() {

	}

}