<?php

namespace YapepBase\Test\Mock\Response;
use \YapepBase\Response\IOutput;

/**
 * @codeCoverageIgnore
 */
class OutputMock implements IOutput {
    public $responseCode    = 200;
    public $responseMessage = "OK";
    public $headers         = array();
    public $cookies         = array();
    public $out             = "";

    /**
     * Cleans all previously set output.
     */
    public function clean() {
        $this->responseCode    = 200;
        $this->responseMessage = "OK";
        $this->headers         = array();
        $this->cookies         = array();
        $this->out             = "";
    }

    /**
     * header() is used to send a raw HTTP header. See the » HTTP/1.1 specification for more information on HTTP headers.
     *
     * @param string $string       The header string to set.
     * @param bool   $replace      If this is set, current headers are replaced.
     *                             Defaults to true.
     * @param int    $responsecode The response code to set.
     *
     */
    public function header($string, $replace = true, $responsecode = null){
        if (preg_match('/HTTP\/1.1 (?P<code>[0-9]+) (?P<message>.*)/', $string,
            $matches)) {
            $this->responseCode = $matches['code'];
            $this->responseMessage = $matches['message'];
        } else {
            list($name, $value) = explode(':', $string, 2);
            $name = trim($name);
            $value = trim($value);
            if (!array_key_exists($string, $this->headers) || $replace) {
                $this->headers[$name] = array();
            }
            $this->headers[$name][] = $value;
        }
    }

    /**
     * Send a cookie to the browser.
     * @param string $name     The name of the cookie.
     * @param string $value    The value of the cookie.
     * @param int    $expire   The time the cookie expires. This is a Unix
     *                         timestamp so is in number of seconds since the
     *                         epoch. In other words, you'll most likely set
     *                         this with the time() function plus the number of
     *                         seconds before you want it to expire. Or you
     *                         might use mktime(). time()+60*60*24*30 will set
     *                         the cookie to expire in 30 days. If set to 0, or
     *                         omitted, the cookie will expire at the end of the
     *                         session (when the browser closes).
     * @param string $path     The path on the server in which the cookie will
     *                         be available on. If set to '/', the cookie will
     *                         be available within the entire domain. If set to
     *                         '/foo/', the cookie will only be available within
     *                         the /foo/ directory and all sub-directories such
     *                         as /foo/bar/ of domain. The default value is the
     *                         current directory that the cookie is being set
     *                         in.
     * @param string $domain   The domain that the cookie is available to. To
     *                         make the cookie available on all subdomains of
     *                         example.com (including example.com itself) then
     *                         you'd set it to '.example.com'. Although some
     *                         browsers will accept cookies without the initial
     *                         ., RFC 2109 requires it to be included. Setting
     *                         the domain to 'www.example.com' or
     *                         '.www.example.com' will make the cookie only
     *                         available in the www subdomain.
     * @param bool   $secure   Indicates that the cookie should only be
     *                         transmitted over a secure HTTPS connection from the
     *                         client. When set to TRUE, the cookie will only be
     *                         set if a secure connection exists.
     * @param bool   $httponly When TRUE the cookie will be made accessible only
     *                         through the HTTP protocol. This means that the
     *                         cookie won't be accessible by scripting
     *                         languages, such as JavaScript. It has been
     *                         suggested that this setting can effectively help
     *                         to reduce identity theft through XSS attacks
     *                         (although it is not supported by all browsers),
     *                         but that claim is often disputed.
     */
    public function setcookie($name, $value = '', $expire = 0, $path = '/',
        $domain = '', $secure = false, $httponly = false) {
        $this->cookies[$name] = array(
            'name' => $name,
            'value' => $value,
            'expire' => $expire,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httponly,
        );
        return true;
    }

    /**
     * Outputs all parameters.
     *
     * @param string $string1 First string to output
     * @param string $string2 Second string to output
     * @param string $stringn n-th string to output
     */
    public function out() {
        foreach (func_get_args() as $string) {
            $this->out .= $string;
        }
    }
}