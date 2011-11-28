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
use YapepBase\Exception\RedirectException;

use YapepBase\Request\HttpRequest;

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

    /** HTML content type */
    const CONTENT_TYPE_HTML = 'text/html';
    /** CSS content type */
    const CONTENT_TYPE_CSS = 'text/css';
    /** Javascript content type */
    const CONTENT_TYPE_JAVASCRIPT = 'appliation/javascript';
    /** JSON content type */
    const CONTENT_TYPE_JSON = 'application/json';
    /** XML content type */
    const CONTENT_TYPE_XML = 'application/xml';

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
     * Stores the status header
     *
     * @var string
     */
    protected $statusHeader;

    /**
     * Stores the content type. {@uses self::CONTENT_TYPE_*}
     *
     * @var string
     */
    protected $contentType;

    /**
     * Stores the content type header
     *
     * @var string
     */
    protected $contentTypeHeader;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->setContentType(self::CONTENT_TYPE_HTML);
        $this->startOutputBuffer();
    }

    /**
     * Starts the output buffer.
     */
    protected function startOutputBuffer() {
        if (Config::getInstance()->get('system.response.gzip', false)) {
            ob_start('ob_gzhandler');
        } else {
            ob_start();
        }
    }

    /**
     * Sends the response
     */
    public function send() {
        if (!empty($this->statusHeader)) {
            header($this->statusHeader);
        }
        header($this->contentType);
        foreach ($this->headers as $header) {
            header($header);
        }
        foreach($this->cookies as $cookie) {
            setcookie($cookie['name'], $cookie['value'], $cookie['expiration'], $cookie['path'], $cookie['domain'],
                $cookie['secure'], $cookie['httpOnly']);
        }
        $obContents = ob_get_contents();
        ob_clean();
        echo $this->body->render($this->contentType);
        echo $obContents;
    }

    /**
     * Instantly outputs an error message.
     *
     * This method is used to signal a fatal error to the client.
     *
     * @throws \YapepBase\Exception\Exception   If called after send()
     */
    public function sendError() {
        $this->setStatusCode(500);
        echo '<h1>Internal server error</h1>';
    }

    /**
     * Sets the response body
     *
     * @param \YapepBase\View\IView $body
     */
    public function setBody(IView $body) {
        // TODO checks
        $this->body = $body;
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
        // TODO default status messages
        $this->statusHeader = 'HTTP/1.1 ' . (int)$statusCode . ' '. $statusMessage;
    }

    /**
     * Sets an HTTP header.
     *
     * @param string $header   The header to set.
     */
    public function addHeader($header) {
        $this->headers[] = $header;
    }

    /**
     * Redirects the client to the specified URL
     *
     * @param string $url            The URL to redirect to.
     * @param int    $redirectCode   The status code for the redirect.
     *
     * @throws \YapepBase\Exception\RedirectException   To stop execution of the controller.
     */
    public function redirect($url, $statusCode = 303) {
        $this->setStatusCode($statusCode);
        $this->addHeader('Location: ' . $url);
        throw new RedirectException($url, RedirectException::TYPE_EXTERNAL);
    }

    /**
     * Sets the content type for the response
     *
     * @param string $type      The content type for the response. {@uses self::CONTENT_TYPE} or any valid content type.
     * @param string $charset   The charset of the response. For HTML content this will be set to the system default
     *                          charset. See config option 'system.defaultCharset'.
     */
    public function setContentType($contentType, $charset = null) {
        $this->contentType = $contentType;
        $this->contentTypeHeader = 'Content-type: ' . $contentType;

        if (self::CONTENT_TYPE_HTML == $contentType && empty($charset)) {
            // For HTML content set the default charset to the sytem default.
            $charset = Config::getInstance()->get('system.defaultCharset', 'UTF-8');
        }

        if (!empty($charset)) {
            $this->contentTypeHeader .= '; charset=' . $charset;
        }
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

}