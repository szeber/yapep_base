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
	 * Sets whether output buffering should be enabled or not.
	 *
	 * By default output buffering is enabled. If disabling this, the output will be echoed instead of using the
	 * output object to send it, so the response object has no control over it. This may cause problems with the
	 * sending of headers for example.
	 * If disabling the buffering it will flush and disable all output buffers that were created after
	 * the initialization of the response object. It will also send all headers that were added.
	 *
	 * @param bool $isEnabled   If TRUE, enables, if FALSE disables the output buffering.
	 *
	 * @return mixed
	 *
	 * @throws \YapepBase\Exception\ParameterException   If the output buffering is already in the specified status.
	 */
	public function setOutputBufferingStatus($isEnabled) {

	}

	/**
	 * Returns TRUE if the output buffering is enabled, FALSE if it's not.
	 *
	 * @return bool
	 */
	public function getOutputBufferingStatus() {

	}

	/**
	 * Clears all previous, not sent output in the buffer.
	 *
	 * @return void
	 */
	public function clearAllOutput() {

	}

}