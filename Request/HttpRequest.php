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
     */
    public function __construct() {
        if (get_magic_quotes_gpc()) {
            $this->getParams = UtilityFunctions::recursiveStripSlashes($_GET);
            $this->postParams = UtilityFunctions::recursiveStripSlashes($_POST);
            $this->cookies = UtilityFunctions::recursiveStripSlashes($_COOKIE);
        } else {
            $this->getParams = $_GET;
            $this->postParams = $_POST;
            $this->cookies = $_COOKIE;
        }
        list($this->targetUri) = explode('?', $_SERVER['REQUEST_URI'], 2);
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

    public function getHeader() {

    }

    public function getEnv() {

    }

    /**
     * Returns the specified request parameter from the specified source, or the default value.
     *
     * Search order is GPC, so a POST value will overwrite a GET value with the same name.
     *
     * @param string $name      The name of the cookie.
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
            $result = $this->getParams[$name];
        }

        if (strstr($source, 'C') && isset($this->cookies[$name])) {
            $result = $this->cookies[$name];
        }

        return $result;
    }



    /**
     * Returns the target of the request.
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
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Sets a route param
     *
     * @param string $name
     * @param mices $value
     */
    public function setParam($name, $value) {
        $this->routeParams[$name] = $value;
    }

    public function isAjaxRequest() {

    }

    public function isIe() {

    }

    public function isGecko() {

    }

    public function isWebkit() {

    }
}