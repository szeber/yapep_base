<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Router
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace YapepBase\Router;
use YapepBase\Exception\RouterException;
use YapepBase\Request\IRequest;

/**
 * ArrayRouter class
 *
 * Routes a request based on the specified associative array.
 * The array's keys are in the Controller/Action format. The controller name should not include the namespace, and the
 * Action's name should not include the controller specific action prefix.
 * The array's values are the routes in the following format: [METHOD]URI
 * The method is optional (if present, the brackets are required). The URI may contain parameters in the format:
 * {paramName:paramType(options)}
 * The valid paramTypes are:
 *     <ul>
 *         <li>num: Any number. No options are allowed.</li>
 *         <li>alpha: Any alphabetic character. No options are allowed.</li>
 *         <li>alnum: Any alphanumeric character. No options are allowed.</li>
 *         <li>enum: Enumeration of the values in the options. The enumeration values should be separated by
 *                   the '|' character in the options string. Any '/' characters should be escaped.</li>
 *         <li>regex: Regular expression. The pattern should be included in the options string. The pattern should
 *                    not contain delimiters, and should be escaped for a '/' delimiter. It may not contain '{' or '}'
 *                    characters.</li>
 *     </ul>
 *
 * @package    YapepBase
 * @subpackage Router
 */
class ArrayRouter implements IRouter {

    /**
     * The request instance
     *
     * @var \YapepBase\Request\IRequest
     */
    protected $request;

    /**
     * The available routes
     *
     * @var array
     */
    protected $routes;

    /**
     * Constructor
     *
     * @param \YapepBase\Request\IRequest $request   The request instance
     * @param array    $routes    The list of available routes
     */
    public function __construct(IRequest $request, array $routes) {
        $this->request = $request;
        $this->routes = $routes;
    }

    /**
     * Returns a controller and an action for the request's target.
     *
     * @param string $controller   $he controller class name. (Outgoing parameter)
     * @param string $action       The action name in the controller class. (Outgoing parameter)
     *
     * @return string   The controller and action separated by a '/' character.
     *
     * @throws RouterException   On errors. (Includig if the route is not found)
     */
    public function getRoute (&$controller = null, &$action = null) {
        $target = $this->request->getTarget();
        $method = $this->request->getMethod();

        // If the target doesn't start with a '/', add one
        if ('/' != substr($target, 0, 1)) {
            $target = '/' . $target;
        }

        foreach ($this->routes as $controllerAction => $path) {
            if ('[' == substr($path, 0, 1)) {
                // This path is restricted to a method
                list($pathMethod, $path) = explode(']', substr($path, 1), 2);
                if ($method != $pathMethod) {
                    // The method does not match, continue with the next path
                    continue;
                }
            }

            $path = trim($path);

            if ('/' != substr($path, 0, 1)) {
                // If the path doesn't start with a '/', add one
                $path = '/' . $path;
            }

            if (false !== ($firstParamPos = strpos($path, '{'))) {
                // This path has params
                if (substr($path, 0, $firstParamPos) != substr($target, 0, $firstParamPos)) {
                    // The static part of the path doesn't match, continue with the next path
                    continue;
                }
                $regex = $this->getRegexForPath($path);
                $params = array();
                if (!preg_match($regex, $target, $params)) {
                    // The target doesn't match the path
                    continue;
                }
                foreach($params as $name => $value) {
                    if (is_numeric($name)) {
                        continue;
                    }
                    $this->request->setParam($name, $value);
                }
                list($controller, $action) = explode('/', $controllerAction, 2);
                return $controllerAction;
            } else {
                if ($path == $target) {
                    list($controller, $action) = explode('/', $controllerAction, 2);
                    return $controllerAction;
                }
            }
        }

        // There was no valid route
        throw new RouterException('No route found for path', RouterException::ERR_NO_ROUTE_FOUND);
    }

    /**
     * Creates a regex from the parameterized route path.
     *
     * @param string $path   The path to process
     *
     * @return string   The regex created for the path
     *
     * @throws RouterException   On errors.
     */
    protected function getRegexForPath($path) {
        $matches = array();
        if (!preg_match_all('/\{([-_.a-zA-Z0-9]+):([-_.a-zA-Z0-9]+)(\(([^}]*)\))?\}/', $path, $matches, PREG_SET_ORDER)) {
            throw new RouterException('Invalid param syntax in route', RouterException::ERR_SYNTAX_PARAM);
        }

        $pathRegex = '/^' . preg_quote($path, '/') . '$/';
        $params = array();
        foreach ($matches as $match) {
            switch ($match[2]) {
                case 'alpha':
                    $pattern = '[[:alpha:]]+';
                    break;

                case 'num':
                    $pattern = '\d+';
                    break;

                case 'alnum':
                    $pattern = '[[:alnum:]]+';
                    break;

                case 'regex':
                    if (empty($match[3])) {
                        throw new RouterException('Regex param type without pattern',
                            RouterException::ERR_SYNTAX_PARAM);
                    }
                    $pattern = $match[3];
                    break;

                case 'enum':
                    if (empty($match[3])) {
                        throw new RouterException('Enum param type without values',
                            RouterException::ERR_SYNTAX_PARAM);
                    }
                    $pattern = $match[3];
                    break;

                default:
                    throw new RouterException('Invalid param type in route: ' . $match[1],
                        RouterException::ERR_SYNTAX_PARAM);
                    break;
            }
            $count = 0;
            $pathRegex = str_replace(preg_quote($match[0], '/'), '(?P<' . preg_quote($match[1], '/') . '>' . $pattern
                . ')', $pathRegex, $count);
            if (1 != $count) {
                throw new RouterException('Duplicate route param name', RouterException::ERR_SYNTAX_PARAM);
            }
        }
        return $pathRegex;
    }

    /**
     * Returns the target (eg. URL) for the controller and action
     *
     * @param string $controller   The name of the controller
     * @param string $action       The name of the action
     * @param array  $params       Associative array with the route params, if they are required.
     *
     * @return string   The target.
     *
     * @throws RouterException   On errors. (Includig if the route is not found)
     */
    public function getTargetForControllerAction($controller, $action, $params = array()) {
        $key = $controller . '/' . $action;
        if (!isset($this->routes[$key])) {
            throw new RouterException('No route found for controller and action', RouterException::ERR_NO_ROUTE_FOUND);
        }
        $target = preg_replace('/^\s*\[[^\]]+\]\s*/', '', $this->routes[$key]);
        if (strstr($this->routes[$key], '{')) {
            foreach($params as $key => $value) {
                $target = preg_replace('/\{' . preg_quote($key, '/') . ':[^}]+\}/', $value, $target);
            }
            if (strstr($target, '{')) {
                throw new RouterException('Missing route params.', RouterException::ERR_MISSING_PARAM);
            }
        }
        if ('/' != substr($target, 0, 1)) {
            $target = '/' . $target;
        }
        return $target;
    }
}