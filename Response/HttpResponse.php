<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Response
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Response;
use YapepBase\View\IView;

use YapepBase\Exception\Exception;

use YapepBase\Exception\RedirectException;

use YapepBase\Request\HttpRequest;

use YapepBase\Mime\MimeType;

use YapepBase\Config;

/**
 * HttpResponse class
 *
 * Configuration options:
 *     <ul>
 *         <li>system.defaultCharset:                 Sets the default charset for the response.
 *                                                    Only used for HTML content types.</li>
 *         <li>system.response.gzip:                  If set to TRUE, enables GZIP compression.</li>
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
     * @var \YapepBase\View\IView $body
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
    protected $statusCode = 200;

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
     * Standard HTTP status codes
     *
     * @var array
     */
    protected static $statusCodes = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    );

    /**
     * Constructor.
     * @param \YapepBase\Response\IOutput $output The output handler to use.
     *                                            Uses PHPOutput if none given.
     */
    public function __construct(IOutput $output = null) {
        $this->setContentType(MimeType::HTML);
        $this->startOutputBuffer();
        // @codeCoverageIgnoreStart
        if (!$output) {
            $output = new PHPOutput();
        }
        // @codeCoverageIgnoreEnd
        $this->output = $output;
    }

    /**
     * Starts the output buffer.
     */
    protected function startOutputBuffer() {
        ob_start();
    }

    /**
     * Checks, if RFC2616 is adhered so browser incompatibilities are avoided.
     * Only for use in the send() function.
     */
    protected function checkStandards($renderedBody) {
        switch ($this->statusCode) {
            case 204:
                /**
                 * If the response code is No Content, a response body must not be returned.
                 */
                if (strlen($renderedBody) > 0) {
                    throw new \YapepBase\Exception\StandardsComplianceException('If a No Content (204) status code is returned, the response body must be empty. (Currently contains ' . strlen($this->body) . ' bytes)');
                }
                break;
            case 206:
                /**
                 * In case of a partial-content the response must contain at least one of the following:
                 * - A Content-Range header
                 * - A Date header
                 */
                if (!$this->hasHeader('Content-Range') || !$this->hasHeader('Date')) {
                    throw new \YapepBase\Exception\StandardsComplianceException('The Partial-Content (206) response requires a Content-Range and a Date header to be set.');
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
                    throw new \YapepBase\Exception\StandardsComplianceException('The ' . $this->statusCode . ' status code require a Location header to be set.');
                }
                break;
            case 304:
                /**
                 * A Date header must be provided
                 */
                if (!$this->hasHeader('Date')) {
                    throw new \YapepBase\Exception\StandardsComplianceException('The 304 status code requires a Date header to be set.');
                }
                break;
            case 401:
                /**
                 * A WWW-Authenticate header must be provided, otherwise Opera will provide strange behaviour.
                 */
                if (!$this->hasHeader('WWW-Authenticate')) {
                    throw new \YapepBase\Exception\StandardsComplianceException('The 401 status code requires a WWW-Authenticate header to be set.');
                }
                break;
            case 405:
                /**
                 * An Allow header must be provided.
                 */
                if (!$this->hasHeader('Allow')) {
                    throw new \YapepBase\Exception\StandardsComplianceException('The 405 status code requires an Allow header to be set.');
                }
                break;
        }
    }

    /**
     * Sends the response
     *
     * @todo make sending non-rendered output configurable (defaults to not sending it)
     */
    public function send() {
        if ($this->isResponseSent) {
            throw new Exception('Send called after the response has been sent');
        }

        $renderedBody = $this->getRenderedBody();

        $this->checkStandards($renderedBody);

        $this->isResponseSent = true;

        $this->output->header('HTTP/1.1 ' . (int)$this->statusCode . ' '. $this->statusMessage);
        foreach ($this->headers as $name => $header) {
            foreach ($header as $value) {
                $this->output->header($name . ': ' . $value);
            }
        }
        foreach($this->cookies as $cookie) {
            $this->output->setcookie($cookie['name'], $cookie['value'], $cookie['expiration'], $cookie['path'], $cookie['domain'],
                $cookie['secure'], $cookie['httpOnly']);
        }
        $obContents = ob_get_contents();
        ob_clean();
        $this->output->out($renderedBody);
        $this->output->out($obContents);
    }

    /**
     * Instantly outputs an error message.
     *
     * This method is used to signal a fatal error to the client.
     *
     * @throws \YapepBase\Exception\Exception   If called after send()
     */
    public function sendError() {
        if ($this->isResponseSent) {
            throw new Exception('SendError called after the response has been sent');
        }

        $this->isResponseSent = true;

        $this->output->header('HTTP/1.1 500 Internal Server Error');
        $this->output->out('<h1>Internal server error</h1>');
    }

    /**
     * Sets the response body
     *
     * @param \YapepBase\View\IView $body
     */
    public function setBody(IView $body) {
        $this->body = $body;
    }

    /**
     * Sets the already rendered body.
     *
     * @param string $body
     */
    public function setRenderedBody($body) {
        $this->body = (string)$body;
    }

    /**
     * Renders and returns the HTTP response body.
     *
     * @return string
     */
    public function getRenderedBody() {
        if ($this->body instanceof IView) {
            return $this->body->render($this->contentType, true);
        } else {
            return $this->body;
        }
    }

    /**
     * Sets the status code for the response
     *
     * @param int    $statusCode      The status code for the response.
     * @param string $statusMessage   The message for the status code.
     *
     * @return return_type
     */
    public function setStatusCode($statusCode, $statusMessage = '') {
        if (!$statusMessage) {
            if (array_key_exists($statusCode, self::$statusCodes)) {
                $statusMessage = self::$statusCodes[$statusCode];
            } else {
                $statusMessage = "Non-Standard Response";
            }
        }
        $this->statusCode = $statusCode;
        $this->statusMessage = $statusMessage;
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
     * @param string|array $header The header to set. If it is an array, every
     *                             part is used as a separate header.
     * @param string $value        The header value to set. If empty, the
     *                             $header will be exploded along a : sign.
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
            if (!$header){
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
            if (!$value){
                throw new \YapepBase\Exception\ParameterException('Value for header is empty: ' . $header);
            }
            if (!array_key_exists($header, $this->headers)) {
                $this->headers[$header] = array();
            }
            $this->headers[$header][] = $value;
        }
    }

    /**
     * Removes one or more headers.
     *
     * @param string|array $header The header to remove.
     */
    public function removeHeader($header) {
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
     * @param string|array $header The header to set. If it is an array, every
     *                             part is used as a separate header.
     * @param string $value        The header value to set. If empty, the
     *                             $header will be exploded along a : sign.
     */
    public function setHeader($header, $value = null) {
        $this->removeHeader($header);
        $this->addHeader($header, $value);
    }

    /**
     * Return an array of values for a header, that has been set previously.
     *
     * @param type $header
     * @return array of string
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
     * @param  string $header A header name
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
     * Sets a cookie with the response. The params are same as for the php setcookie() function.
     * {@link http://php.net/setcookie}
     *
     * @param string $name
     * @param string $value
     * @param int    $expiration
     * @param string $path
     * @param string $domain
     * @param bool   $secure
     * @param bool   $httpOnly
     */
    public function setCookie(
        $name, $value, $expiration = 0, $path = '/', $domain = null, $secure = false, $httpOnly = false
    ) {
        if (is_null($domain)) {
            $domain = Config::getInstance()->get('system.response.defaultCookieDomain', null);
        }
        $this->cookies[$name] = array(
            'name'       => $name,
            'value'      => $value,
            'expiration' => $expiration,
            'path'       => $path,
            'domain'     => $domain,
            'secure'     => $secure,
            'httpOnly'   => $httpOnly,
        );
    }

    /**
     * Checks, if a cookie has been set.
     * @param string $name The cookie to check
     * @return bool
     */
    public function hasCookie($name) {
        return array_key_exists($name, $this->cookies);
    }
}