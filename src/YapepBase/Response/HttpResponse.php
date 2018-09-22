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

use Lukasoppermann\Httpstatus\Httpstatuscodes;
use YapepBase\Application;
use YapepBase\Config;
use YapepBase\Exception\Exception;
use YapepBase\Exception\ParameterException;
use YapepBase\Exception\RedirectException;
use YapepBase\Mime\MimeType;
use YapepBase\View\ViewAbstract;

/**
 * HttpResponse class
 *
 * Configuration options:
 *     <ul>
 *         <li>system.defaultCharset:                 Sets the default charset for the response.
 *                                                    Only used for HTML content types.</li>
 *         <li>system.response.defaultCookieDomain:   The default domain for the cookies.</li>
 *     </ul>
 *
 * @package    YapepBase
 * @subpackage Response
 */
class HttpResponse implements IResponse {

	/**
	 * The response body.
	 *
	 * @var \YapepBase\View\ViewAbstract $body
	 */
	protected $body;

	/**
	 * Stores te cookies to be set in the  response.
	 *
	 * @var array
	 */
	protected $cookies = array();

	/**
	 * Stores the headers to be sent out in the response.
	 *
	 * @var array
	 */
	protected $headers = array();

	/**
	 * Stores the status code
	 * @var int
	 * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
	 */
	protected $statusCode = Httpstatuscodes::HTTP_OK;

	/**
	 * Stores the status message
	 *
	 * @var string
	 */
	protected $statusMessage = 'OK';

	/**
	 * Stores the content type. {@uses MimeType::*}
	 *
	 * @var string
	 */
	protected $contentType;

	/**
	 * TRUE if the response has already been sent.
	 *
	 * @var bool
	 */
	protected $isResponseSent = false;

	/**
	 * Stores the raw output handler.
	 *
	 * @var IOutput
	 */
	protected $output;

	/**
	 * Stores the ob level for the status when the response was created.
	 *
	 * @var int
	 */
	protected $startingObLevel;

	/**
	 * Stores whether the output buffering is enabled.
	 *
	 * @var bool
	 */
	protected $isBufferingEnabled;

	/**
	 * Constructor.
	 *
	 * @param \YapepBase\Response\IOutput $output   The output handler to use.
	 *                                              Uses PhpOutput if none given.
	 */
	public function __construct(IOutput $output = null) {
		// @codeCoverageIgnoreStart
		if (!$output) {
			$output = new PhpOutput();
		}
		// @codeCoverageIgnoreEnd
		$this->output = $output;
		$this->startOutputBuffer();
		$this->setContentType(MimeType::HTML);
	}

	/**
	 * Starts the output buffer.
	 *
	 * @return void
	 */
	protected function startOutputBuffer() {
		$this->isBufferingEnabled = true;
		if (is_null($this->startingObLevel)) {
			$this->startingObLevel = ob_get_level();
		}
		ob_start();
	}

	/**
	 * Disables output buffering and sends all output.
	 *
	 * @return void
	 */
	protected function disableOutputBuffer() {
		$this->isBufferingEnabled = false;

		$this->sendHeaders();

		$this->headers = array();
		$this->cookies = array();

		while (ob_get_level() > $this->startingObLevel) {
			$this->output->out(ob_get_clean());
		}
		$this->startingObLevel = null;
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
		if ($isEnabled == $this->isBufferingEnabled) {
			throw new ParameterException('Trying to set the output buffer status to '
			. ($isEnabled ? 'enabled' : 'disabled') . ', but it is already in that status');
		}

		if ($isEnabled) {
			$this->startOutputBuffer();
		} else {
			$this->disableOutputBuffer();
		}
	}

	/**
	 * Returns TRUE if the output buffering is enabled, FALSE if it's not.
	 *
	 * @return bool
	 */
	public function getOutputBufferingStatus() {
		return $this->isBufferingEnabled;
	}

	/**
	 * Checks, if RFC2616 is adhered so browser incompatibilities are avoided.
	 * Only for use in the send() function.
	 *
	 * @param string $renderedBody   The rendered body.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\StandardsComplianceException   If a standards compliance problem is found.
	 */
	protected function checkStandards($renderedBody) {
		switch ($this->statusCode) {
			case 204:
				/**
				 * If the response code is No Content, a response body must not be returned.
				 */
				if (strlen($renderedBody) > 0) {
					throw new \YapepBase\Exception\StandardsComplianceException(
						'If a No Content (204) status code is returned, the response body must be empty. '
						. '(Currently contains ' . strlen($this->body) . ' bytes)');
				}
				break;
			case 206:
				/**
				 * In case of a partial-content the response must contain at least one of the following:
				 * - A Content-Range header
				 * - A Date header
				 */
				if (!$this->hasHeader('Content-Range') || !$this->hasHeader('Date')) {
					throw new \YapepBase\Exception\StandardsComplianceException(
						'The Partial-Content (206) response requires a Content-Range and a Date header to be set.');
				}
				break;
			case 301:
			case 302:
			case 303:
			case 305:
			case 307:
				/**
				 * A Location header field must be provided.
				 */
				if (!$this->hasHeader('Location')) {
					throw new \YapepBase\Exception\StandardsComplianceException('The ' . $this->statusCode
						. ' status code require a Location header to be set.');
				}
				break;
			case 304:
				/**
				 * A Date header must be provided
				 */
				if (!$this->hasHeader('Date')) {
					throw new \YapepBase\Exception\StandardsComplianceException(
						'The 304 status code requires a Date header to be set.');
				}
				break;
			case 401:
				/**
				 * A WWW-Authenticate header must be provided, otherwise Opera will provide strange behaviour.
				 */
				if (!$this->hasHeader('WWW-Authenticate')) {
					throw new \YapepBase\Exception\StandardsComplianceException(
						'The 401 status code requires a WWW-Authenticate header to be set.');
				}
				break;
			case 405:
				/**
				 * An Allow header must be provided.
				 */
				if (!$this->hasHeader('Allow')) {
					throw new \YapepBase\Exception\StandardsComplianceException(
						'The 405 status code requires an Allow header to be set.');
				}
				break;
		}
	}

	/**
	 * Sends the response
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\Exception   If the response is already sent.
	 *
	 * @todo make sending non-rendered output configurable (defaults to not sending it)
	 */
	public function send() {
		if ($this->isResponseSent) {
			throw new Exception('Send called after the response has been sent');
		}

		$renderedBody = $this->getRenderedBody();

		if ($this->isBufferingEnabled) {
			// If OB is disabled, output is sent immediately, so headers are already sent
			$this->checkStandards($renderedBody);

			$this->isResponseSent = true;

			$this->sendHeaders();
		}
		$obContents = '';
		while ($this->isBufferingEnabled && ob_get_level() > $this->startingObLevel) {
			$obContents .= ob_get_clean();
		}
		$this->output->out($renderedBody);
		$this->output->out($obContents);
	}

	/**
	 * Instantly outputs an error message.
	 *
	 * This method is used to signal a fatal error to the client.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\Exception   If called after send()
	 */
	public function sendError() {
		if ($this->isResponseSent) {
			throw new Exception('SendError called after the response has been sent');
		}

		$this->isResponseSent = true;

		$this->output->setHeader('HTTP/1.1 500 Internal Server Error');
		$this->output->out('<h1>Internal server error</h1>');
	}

	/**
	 * Sets the response body
	 *
	 * @param \YapepBase\View\ViewAbstract $body   The body.
	 *
	 * @return void
	 */
	public function setBody(ViewAbstract $body) {
		$this->body = $body;
	}

	/**
	 * Sets the already rendered body.
	 *
	 * @param string $body   The rendered body.
	 *
	 * @return void
	 */
	public function setRenderedBody($body) {
		$this->body = (string)$body;
	}

	/**
	 * Renders the output.
	 *
	 * @return void
	 */
	public function render() {
		if (!empty($this->body) && !is_string($this->body)) {
			$this->body = $this->body->toString();
		}
	}

	/**
	 * Renders and returns the HTTP response body.
	 *
	 * @return string
	 */
	public function getRenderedBody() {
		if (empty($this->body) || is_string($this->body)) {
			return (string)$this->body;
		}
		return $this->body->toString();
	}

	/**
	 * Sets the status code for the response
	 *
	 * @param int    $statusCode      The status code for the response.
	 * @param string $statusMessage   The message for the status code.
	 *
	 * @return void
	 */
	public function setStatusCode($statusCode, $statusMessage = '') {
		if (!$statusMessage) {
			$httpStatus = Application::getInstance()->getDiContainer()->getHttpStatus();

			if ($httpStatus->hasStatusCode($statusCode)) {
				$statusMessage = $httpStatus->getReasonPhrase($statusCode);
			} else {
				$statusMessage = 'Non-Standard Response';
			}
		}
		$this->statusCode = $statusCode;
		$this->statusMessage = $statusMessage;
		if (!$this->isBufferingEnabled) {
			$this->output->setHeader('HTTP/1.1 ' . (int)$this->statusCode . ' ' . $this->statusMessage);
		}
	}

	/**
	 * Returns the currently set status code.
	 *
	 * @return int
	 */
	public function getStatusCode() {
		return $this->statusCode;
	}

	/**
	 * Returns the currently set status message.
	 *
	 * @return string
	 */
	public function getStatusMessage() {
		return $this->statusMessage;
	}

	/**
	 * Sets an HTTP header.
	 *
	 * @param string|array $header   The header to set. If it is an array, every
	 *                               part is used as a separate header.
	 * @param string       $value    The header value to set. If empty, the
	 *                               $header will be exploded along a : sign.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\ParameterException if an invalid header
	 *         configuration occurs
	 */
	public function addHeader($header, $value = null) {
		if (is_array($header)) {
			foreach ($header as $headername => $headervalue) {
				if (!is_string($headername)) {
					$this->addHeader($headervalue);
				} else {
					$this->addHeader($headername, $headervalue);
				}
			}
		} else {
			if (!$header) {
				throw new \YapepBase\Exception\ParameterException('Header name is empty.');
			}
			if (is_null($value)) {
				$data = explode(':', $header, 2);
				if (!array_key_exists(1, $data)) {
					throw new \YapepBase\Exception\ParameterException('Invalid header line: ' . $value);
				}
				$header = trim($data[0]);
				$value = trim($data[1]);
			}
			/**
			 * Technically this is correct, but it's not nice. We don't allow it
			 * to avoid user agent bugs.
			 */
			if (!$value) {
				throw new \YapepBase\Exception\ParameterException('Value for header is empty: ' . $header);
			}
			if ($this->isBufferingEnabled) {
				if (!array_key_exists($header, $this->headers)) {
					$this->headers[$header] = array();
				}
				$this->headers[$header][] = $value;
			} else {
				$this->output->setHeader($header . ':' . $value);
			}
		}
	}

	/**
	 * Removes one or more headers.
	 *
	 * If output buffering is disabled all headers are sent immediately, so it's not possible to remove any headers.
	 *
	 * @param string|array $header   The header to remove.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\Exception   If output buffering is disabled.
	 */
	public function removeHeader($header) {
		if (!$this->isBufferingEnabled) {
			throw new Exception('Output buffering is disabled, so it is not possible to remove a header');
		}
		if (is_array($header)) {
			foreach ($header as $h) {
				$this->removeHeader($h);
			}
		} else {
			$data = explode(':', $header, 2);
			if ($this->hasHeader($data[0])) {
				unset($this->headers[$data[0]]);
			}
		}
	}

	/**
	 * This function removes all previous values of a header and sets the new
	 * values.
	 *
	 * @param string|array $header   The header to set. If it is an array, every
	 *                               part is used as a separate header.
	 * @param string       $value    The header value to set. If empty, the
	 *                               $header will be exploded along a : sign.
	 *
	 * @return void
	 */
	public function setHeader($header, $value = null) {
		if ($this->isBufferingEnabled) {
			$this->removeHeader($header);
		}
		$this->addHeader($header, $value);
	}

	/**
	 * Return an array of values for a header, that has been set previously.
	 *
	 * If the output buffering is disabled, all headers are immediately sent, so it's not possible to get their value,
	 * so in this case this method will always throw an exception.
	 *
	 * @param string $header   The header name.
	 *
	 * @return array
	 *
	 * @throws \YapepBase\Exception\IndexOutOfBoundsException   If the header has not been set.
	 */
	public function getHeader($header) {
		if (!$this->hasHeader($header)) {
			throw new \YapepBase\Exception\IndexOutOfBoundsException($header);
		}
		return $this->headers[$header];
	}

	/**
	 * Returns, if a header has been set.
	 *
	 * If the output buffering is disabled, all headers are immediately sent, so this method will always return FALSE.
	 *
	 * @param string $header   A header name
	 *
	 * @return bool
	 */
	public function hasHeader($header) {
		return array_key_exists($header, $this->headers);
	}

	/**
	 * Redirects the client to the specified URL
	 *
	 * @param string $url          The URL to redirect to.
	 * @param int    $statusCode   The status code for the redirect.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\RedirectException   To stop execution of the controller.
	 */
	public function redirect($url, $statusCode = 303) {
		$this->setStatusCode($statusCode);
		$this->setHeader('Location', $url);
		throw new RedirectException($url, RedirectException::TYPE_EXTERNAL);
	}

	/**
	 * Sets the content type for the response
	 *
	 * @param string $contentType   The content type for the response. {@uses MimeType::*} or any valid content
	 *                              type.
	 * @param string $charset       The charset of the response. For HTML content this will be set to the system default
	 *                              charset. See config option 'system.defaultCharset'.
	 *
	 * @return void
	 */
	public function setContentType($contentType, $charset = null) {
		$this->contentType = $contentType;
		$contentTypeHeader = $contentType;

		if ((MimeType::HTML == $contentType || MimeType::XHTML == $contentType) && empty($charset)) {
			// For (X)HTML content set the default charset to the sytem default.
			$charset = Config::getInstance()->get('system.defaultCharset', 'UTF-8');
		}

		if (!empty($charset)) {
			$contentTypeHeader .= '; charset=' . $charset;
		}
		$this->setHeader('Content-Type', $contentTypeHeader);
	}

	/**
	 * Returns the contentType of the current response.
	 *
	 * @return string   The content type for the response. {@uses MimeType::*}
	 */
	public function getContentType() {
		return $this->contentType;
	}

	/**
	 * Sets a cookie with the response. The params are same as for the php setCookie() function.
	 * {@link http://php.net/setCookie}
	 *
	 * @param string $name         Cookie name.
	 * @param string $value        The cookie value.
	 * @param int    $expiration   The expiration timestamp.
	 * @param string $path         Path.
	 * @param string $domain       Cookie domain.
	 * @param bool   $secure       Is the cookie HTTPS only.
	 * @param bool   $httpOnly     Is the cookie HTTP only.
	 *
	 * @return void
	 */
	public function setCookie(
		$name, $value, $expiration = 0, $path = '/', $domain = null, $secure = false, $httpOnly = false
	) {
		if (is_null($domain)) {
			$domain = Config::getInstance()->get('system.response.defaultCookieDomain', false);
			if (false === $domain) {
				$domain = null;
			}
		}

		if ($this->isBufferingEnabled) {
			$this->cookies[$name] = array(
				'name'       => $name,
				'value'      => $value,
				'expiration' => $expiration,
				'path'       => $path,
				'domain'     => $domain,
				'secure'     => $secure,
				'httpOnly'   => $httpOnly,
			);
		} else {
			$this->output->setCookie($name, $value, $expiration, $path, $domain, $secure, $httpOnly);
		}
	}

	/**
	 * Checks, if a cookie has been set.
	 *
	 * @param string $name   The cookie to check
	 *
	 * @return bool
	 */
	public function hasCookie($name) {
		return array_key_exists($name, $this->cookies);
	}

	/**
	 * Clears all previous, not sent output in the buffer.
	 *
	 * @return void
	 */
	public function clearAllOutput() {
		while (ob_get_level() > $this->startingObLevel) {
			ob_end_clean();
		}
		$this->startOutputBuffer();
	}

	/**
	 * Sends all headers (including cookie headers).
	 *
	 * @return void
	 */
	protected function sendHeaders() {
		$this->output->setHeader('HTTP/1.1 ' . (int)$this->statusCode . ' ' . $this->statusMessage);
		foreach ($this->headers as $name => $header) {
			foreach ($header as $value) {
				$this->output->setHeader($name . ': ' . $value);
			}
		}
		foreach ($this->cookies as $cookie) {
			$this->output->setCookie($cookie['name'], $cookie['value'], $cookie['expiration'], $cookie['path'],
				$cookie['domain'], $cookie['secure'], $cookie['httpOnly']);
		}
	}

}
