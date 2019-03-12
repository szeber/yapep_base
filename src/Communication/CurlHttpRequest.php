<?php
declare(strict_types = 1);
/**
 * This file is part of YAPEPBase.
 *
 * @copyright  2011 The YAPEP Project All rights reserved.
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
namespace YapepBase\Communication;

use YapepBase\Application;
use YapepBase\Debugger\Item\CurlRequestItem;
use YapepBase\Exception\CurlException;
use YapepBase\Exception\ParameterException;

/**
 * Wrapper class for HTTP requests via CURL.
 */
class CurlHttpRequest
{
    /** GET method */
    const METHOD_GET = 'GET';
    /** HEAD method */
    const METHOD_HEAD = 'HEAD';
    /** POST method */
    const METHOD_POST = 'POST';
    /** PUT method */
    const METHOD_PUT = 'PUT';
    /** DELETE method */
    const METHOD_DELETE = 'DELETE';
    /** TRACE method */
    const METHOD_TRACE = 'TRACE';
    /** OPTIONS method */
    const METHOD_OPTIONS = 'OPTIONS';
    /** CONNECT method */
    const METHOD_CONNECT = 'CONNECT';
    /** PATCH method */
    const METHOD_PATCH = 'PATCH';

    /** Form encoded type. The data will be form encoded before sending. The data must be an associative array. */
    const PAYLOAD_TYPE_FORM_ENCODED = 'formEncoded';
    /** Query string. The data must be an associative array and will be converted to a query string before sending.  */
    const PAYLOAD_TYPE_QUERY_STRING_ARRAY = 'queryStringArray';
    /** Raw payload. The data will be sent as is. The data must be a string. */
    const PAYLOAD_TYPE_RAW = 'raw';

    /**
     * The URL for the request.
     *
     * @var string
     */
    protected $url;

    /**
     * The method for the request.
     *
     * @var string
     */
    protected $method = self::METHOD_GET;

    /**
     * The payload.
     *
     * @var string|array
     */
    protected $payload;

    /**
     * The payload type.
     *
     * @var string
     */
    protected $payloadType;

    /**
     * The headers that need to be sent.
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Any additional curl options to set.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Any cookies to be sent in the request.
     *
     * @var array
     */
    protected $cookies = [];

    /**
     * Sets whether the request will be registered in the debugger.
     *
     * @var bool
     */
    protected $debuggerDisabled = false;

    /**
     * Sets the URL for the request.
     *
     * @param string $url   The URL for the request.
     *
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = (string)$url;
    }

    /**
     * The method for the request.
     *
     * @param string $method   The method. {@uses SELF::METHOD_*}
     *
     * @return void
     *
     * @throws CurlException   If the method is invalid.
     */
    public function setMethod($method)
    {
        if (!$this->isMethodValid($method)) {
            throw new CurlException('Invalid method: ' . $method);
        }

        $this->method = $method;
    }

    /**
     * Adds a header to the request.
     *
     * @param string $header   The header.
     *
     * @return void
     */
    public function addHeader($header)
    {
        $this->headers[] = $header;
    }

    /**
     * Sets the headers for the request, overwriting any previously set headers.
     *
     * @param array $headers   The headers.
     *
     * @return void
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }

    /**
     * Adds a header to the request.
     *
     * @param string $name    The option's name.
     * @param mixed  $value   The option's value.
     *
     * @return void
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * Sets a single cookie.
     *
     * @param string $name    The cookie's name.
     * @param string $value   The cookie's value.
     *
     * @return void
     */
    public function setCookie($name, $value)
    {
        $this->cookies[$name] = $value;
    }

    /**
     * Sets cookies from an associative array removing any previously set cookies.
     *
     * @param array $cookies   The cookies to set.
     *
     * @return void
     */
    public function setCookies(array $cookies)
    {
        $this->cookies = $cookies;
    }

    /**
     * Sets whether the debuger is enabled or disabled.
     *
     * @param bool $debuggerDisabled   Whether the debugger is enabled or disabled.
     *
     * @return void
     */
    public function setDebuggerDisabled($debuggerDisabled)
    {
        $this->debuggerDisabled = (bool)$debuggerDisabled;
    }

    /**
     * Sets the payload for the request.
     *
     * For GET request the payload will become part of the URL, so probably only the query string array type should
     * be used. Raw is also usable, but should not contain the leading "?" as it will be added when rebuilding the URL.
     *
     * @param string|array $payload        The payload for the request.
     * @param string       $payloadType    The payload type. {@uses self::PAYLOAD_TYPE_*}
     *
     * @return void
     * @throws ParameterException
     */
    public function setPayload($payload, $payloadType)
    {
        switch ($payloadType) {
            case self::PAYLOAD_TYPE_FORM_ENCODED:
            case self::PAYLOAD_TYPE_QUERY_STRING_ARRAY:
                if (!is_array($payload)) {
                    throw new ParameterException('The payload must be an array for the ' . $payloadType . ' type. '
                        . gettype($payload) . ' received');
                }
                break;

            case self::PAYLOAD_TYPE_RAW:
                $payload = (string)$payload;
                break;

            default:
                throw new ParameterException('Invalid payload type: ' . $payloadType);
                break;
        }

        $this->payload     = $payload;
        $this->payloadType = $payloadType;
    }

    /**
     * Sends the request and returns the result object.
     *
     * @return CurlHttpRequestResult
     * @throws CurlException
     */
    public function send()
    {
        if (empty($this->url)) {
            throw new CurlException('Trying to send an HTTP request without setting the URL');
        }

        if (!$this->isMethodValid($this->method)) {
            throw new CurlException('Trying to send an HTTP request with an invalid method: ' . $this->method);
        }

        $options = $this->options;

        $options[CURLOPT_RETURNTRANSFER] = true;
        $options[CURLOPT_HEADER]         = true;

        $url = $this->url;

        switch ($this->method) {
            case self::METHOD_GET:
            case self::METHOD_HEAD:
                if ($this->method == self::METHOD_GET) {
                    $options[CURLOPT_HTTPGET] = true;
                } else {
                    $options[CURLOPT_CUSTOMREQUEST] = $this->method;
                }

                $query = empty($this->payloadType) ? null : $this->getProcessedPayload();

                if (!empty($query)) {
                    if (!is_string($query)) {
                        throw new CurlException('Non string processed query string received for a ' . $this->method
                            . ' request');
                    }
                    $urlParts = parse_url($url);
                    if (false === $urlParts || empty($urlParts['scheme']) || empty($urlParts['host'])) {
                        throw new CurlException('Invalid URL: ' . $url);
                    }
                    $urlParts['query'] = empty($urlParts['query']) ? $query : $query . '&' . $urlParts['query'];

                    // Rebuild the URL. If we have pecl_http with http_build_url use it,
                    // otherwise use the PHP implementation.
                    $url = function_exists('http_build_url')
                        ? http_build_url($urlParts)
                        : $this->buildUrl($urlParts);
                }
                break;

            case self::METHOD_POST:
            default:
                if ($this->method == self::METHOD_POST) {
                    $options[CURLOPT_POST] = true;
                } else {
                    $options[CURLOPT_CUSTOMREQUEST] = $this->method;
                }

                if (!empty($this->payloadType)) {
                    $payload = $this->getProcessedPayload();
                    if (!empty($payload)) {
                        $options[CURLOPT_POSTFIELDS] = $payload;
                    }
                }
        }

        if (!empty($this->headers)) {
            $options[CURLOPT_HTTPHEADER] = array_values($this->headers);
        }

        $curl = curl_init($url);

        if (!empty($this->cookies)) {
            $cookies = [];
            foreach ($this->cookies as $name => $value) {
                $cookies[] = $name . '=' . $value;
            }
            curl_setopt($curl, CURLOPT_COOKIE, implode('; ', $cookies));
        }

        curl_setopt_array($curl, $options);

        $debugger = ($this->debuggerDisabled) ? false : Application::getInstance()->getDiContainer()->getDebugger();

        // If we have a debugger, we have to log the query
        $startTime = microtime(true);
        $result    = curl_exec($curl);

        if ($debugger !== false) {
            $debugger->addItem(new CurlRequestItem(
                CurlRequestItem::PROTOCOL_HTTP,
                $this->method,
                $this->url,
                (array)$this->payload,
                $this->headers,
                $this->options,
                microtime(true) - $startTime
            ));
        }

        $error = null;
        $info  = [];

        if (false === $result) {
            $error = curl_error($curl);
        } else {
            $info = curl_getinfo($curl);
        }

        curl_close($curl);

        return new CurlHttpRequestResult($result, $info, $error);
    }

    /**
     * Returns the processed payload.
     *
     * @return array|string
     * @throws CurlException
     */
    protected function getProcessedPayload()
    {
        switch ($this->payloadType) {
            case self::PAYLOAD_TYPE_FORM_ENCODED:
                $result = [];
                $this->flattenPayload($this->payload, $result);

                return $result;
                break;

            case self::PAYLOAD_TYPE_QUERY_STRING_ARRAY:
                return http_build_query($this->payload);
                break;

            case self::PAYLOAD_TYPE_RAW:
                return $this->payload;
                break;

            default:
                throw new CurlException('Unknown payload type: ' . $this->payloadType);
                break;
        }
    }

    /**
     * Flattens the given payload array for sending in the request as form encoded.
     *
     * @param array  $payload           The payload.
     * @param array  $output            The result will be populated here.
     * @param string $paramNamePrefix   Prefix for the parameter name.
     *
     * @return void
     */
    protected function flattenPayload(array $payload, array &$output, $paramNamePrefix = null)
    {
        foreach ($payload as $key => $value) {
            $currentKey = !empty($paramNamePrefix)
                ? $paramNamePrefix . '[' . $key . ']'
                : $key;

            if (is_array($value)) {
                $this->flattenPayload($value, $output, $currentKey);
            } else {
                $output[$currentKey] = (string)$value;
            }
        }
    }

    /**
     * Builds an url from an array returned by parse_url. Use http_build_url if it's available on the system.
     *
     * @param array $urlParts   The parts of the URL.
     *
     * @throws \YapepBase\Exception\CurlException   In case of invalid data given.
     *
     * @return string
     */
    protected function buildUrl($urlParts)
    {
        if (empty($urlParts['host'])) {
            throw new CurlException('Building URL without a host');
        }
        $url = (empty($urlParts['scheme']) ? 'http' : $urlParts['scheme']) . '://';
        if (!empty($urlParts['user'])) {
            $url .= $urlParts['user'];
            if (!empty($urlParts['pass'])) {
                $url .= ':' . $urlParts['pass'];
            }
            $url .= '@';
        }
        $url .= $urlParts['host'];
        if (!empty($urlParts['port'])) {
            $url .= ':' . $urlParts['port'];
        }
        if (!empty($urlParts['path'])) {
            $url .= $urlParts['path'];
        }
        if (!empty($urlParts['query'])) {
            $url .= '?' . $urlParts['query'];
        }
        if (!empty($urlParts['fragment'])) {
            $url .= '#' . $urlParts['fragment'];
        }

        return $url;
    }

    /**
     * Returns TRUE if the specified method is a valid method for the request.
     *
     * @param string $method   The method. {@uses self::METHOD_*}
     *
     * @return bool
     */
    protected function isMethodValid($method)
    {
        return in_array($method, [
            self::METHOD_GET,
            self::METHOD_HEAD,
            self::METHOD_POST,
            self::METHOD_PUT,
            self::METHOD_DELETE,
            self::METHOD_DELETE,
            self::METHOD_TRACE,
            self::METHOD_OPTIONS,
            self::METHOD_CONNECT,
            self::METHOD_PATCH,
        ]);
    }
}
