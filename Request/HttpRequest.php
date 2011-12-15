<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Request
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Request;
use YapepBase\UtilityFunctions;

/**
 * HttpRequest class
 *
 * @package    YapepBase
 * @subpackage Request
 * @todo $_FILES handling
 */
class HttpRequest implements IRequest {

    /**
     * The GET parameters received with the request.
     *
     * @var array
     */
    protected $getParams;

    /**
     * The POST parameters received with the request.
     *
     * @var array
     */
    protected $postParams;

    /**
     * The cookies received with the request.
     *
     * @var array
     */
    protected $cookies;

    /**
     * The information of the files uploaded with the request.
     *
     * @var array
     */
    protected $files;

    /**
     * The server array.
     *
     * @var array
     */
    protected $server;

    /**
     * The environment array.
     *
     * @var array
     */
    protected $env;

    /**
     * The target URI
     *
     * @var string
     */
    protected $targetUri;

    /**
     * The route params
     *
     * @var array
     */
    protected $routeParams = array();

    /**
     * Constructor.
     *
     * @param array $get                  The $_GET array
     * @param array $post                 The $_POST array
     * @param array $cookie               The $_COOKIE array
     * @param array $server               The $_SERVER array
     * @param array $env                  The $_ENV array
     * @param array $files                The $_FILES array
     * @param bool  $magicQuotesEnabled   If TRUE, the get, post, cookie arrays will be recursively stripped of slashes.
     */
    public function __construct(
        array $get, array $post, array $cookie, array $server, array $env, array $files, $magicQuotesEnabled = null
    ) {
        if (is_null($magicQuotesEnabled)) {
            $magicQuotesEnabled = \get_magic_quotes_gpc();
        }

        if ($magicQuotesEnabled) {
            $get = UtilityFunctions::recursiveStripSlashes($get);
            $post = UtilityFunctions::recursiveStripSlashes($post);
            $cookie = UtilityFunctions::recursiveStripSlashes($cookie);
        }

        $this->getParams = $get;
        $this->postParams = $post;
        $this->cookies = $cookie;
        $this->server = $server;
        $this->env = $env;
        $this->files = $files;

        list($this->targetUri) = explode('?', $this->server['REQUEST_URI'], 2);
    }

    /**
     * Returns the GET parameter specified, or the default value, if it's not set.
     *
     * @param string $name      The name of the parameter.
     * @param mixed  $default   The default value, if the parameter is not set.
     *
     * @return mixed
     */
    public function getGet($name, $default = null) {
        if (isset($this->getParams[$name])) {
            return $this->getParams[$name];
        }
        return $default;
    }

    /**
     * Returns the POST parameter specified, or the default value, if it's not set.
     *
     * @param string $name      The name of the parameter.
     * @param mixed  $default   The default value, if the parameter is not set.
     *
     * @return mixed
     */
    public function getPost($name, $default = null) {
        if (isset($this->postParams[$name])) {
            return $this->postParams[$name];
        }
        return $default;
    }

    /**
     * Returns the specified cookie, or the default value, if it's not set.
     *
     * @param string $name      The name of the cookie.
     * @param mixed  $default   The default value, if the parameter is not set.
     *
     * @return mixed
     */
    public function getCookie($name, $default = null) {
        if (isset($this->cookies[$name])) {
            return $this->cookies[$name];
        }
        return $default;
    }

    /**
     * Retruns the specified route param, or the default value if it's not set.
     *
     * @param string $name      The name of the cookie.
     * @param mixed  $default   The default value, if the parameter is not set.
     *
     * @return mixed
     */
    public function getParam($name, $default = null) {
        if (isset($this->routeParams[$name])) {
            return $this->routeParams[$name];
        }
        return $default;
    }

    /**
     * Returns a value from the PHP server array.
     *
     * @param string $name      The key of the value to return.
     * @param mixed  $default   The default value, if the key is not set.
     *
     * @return mixed   The value, or the provided default, if the key is not found.
     */
    public function getServer($name, $default = null) {
        if (isset($this->server[$name])) {
            return $this->server[$name];
        }
        return $default;
    }

    /**
     * Returns a value from the running environment.
     *
     * @param string $name      The key of the value to return.
     * @param mixed  $default   The default value, if the key is not set.
     *
     * @return mixed   The value, or the provided default, if the key is not found.
     */
    public function getEnv($name, $default = null) {
        if (isset($this->env[$name])) {
            return $this->env[$name];
        }
        return $default;
    }

    /**
     * Returns the specified request parameter from the specified source, or the default value.
     *
     * Search order is GPC, so a POST value will overwrite a GET value with the same name.
     *
     * @param string $name      The name of the param.
     * @param mixed  $default   The default value, if the parameter is not set.
     * @param string $source    The sources of the parameter. 'G' for GET, 'P' for POST, 'C' for Cookie.
     *
     * @return mixed
     */
    public function get($name, $default = null, $source = 'GP') {
        $source = strtoupper($source);
        $result = $default;

        if (strstr($source, 'G') && isset($this->getParams[$name])) {
            $result = $this->getParams[$name];
        }

        if (strstr($source, 'P') && isset($this->postParams[$name])) {
            $result = $this->postParams[$name];
        }

        if (strstr($source, 'C') && isset($this->cookies[$name])) {
            $result = $this->cookies[$name];
        }

        return $result;
    }

    /**
     * Returns the target of the request.  (eg the URI for HTTP requests)
     *
     * @return string   The target of the request.
     */
    public function getTarget() {
        return $this->targetUri;
    }

    /**
     * Returns the method of the request
     *
     * @return string   {@uses self::METHOD_*}
     */
    public function getMethod() {
        return $this->server['REQUEST_METHOD'];
    }

    /**
     * Sets a route param
     *
     * @param string $name
     * @param mixed $value
     */
    public function setParam($name, $value) {
        $this->routeParams[$name] = $value;
    }

    /**
     * Returns TRUE if the request was made as an AJAX request.
     *
     * @return bool
     */
    public function isAjaxRequest() {
        return (!empty($this->server['HTTP_X_REQUESTED_WITH'])
            && 'xmlhttprequest' == strtolower($this->server['HTTP_X_REQUESTED_WITH']));
    }
}