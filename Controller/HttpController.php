<?php
/**
 * This file is part of YAPEPBase.
 *
 * @package      YapepBase
 * @subpackage   Controller
 * @author       Zsolt Szeberenyi <szeber@yapep.org>
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */


namespace YapepBase\Controller;
use YapepBase\Application;

use YapepBase\Response\HttpResponse;
use YapepBase\Exception\ControllerException;
use YapepBase\Request\HttpRequest;
use YapepBase\Response\IResponse;
use YapepBase\Request\IRequest;
use YapepBase\Mime\MimeType;


/**
 * HttpController class
 *
 * @package    YapepBase
 * @subpackage Controller
 */
abstract class HttpController extends BaseController {

    /**
     * The request instance
     *
     * @var \YapepBase\Request\HttpRequest
     */
    protected $request;

    /**
     * The response instance
     *
     * @var \YapepBase\Response\HttpResponse
     */
    protected $response;

    /**
     * Constructor.
     *
     * @param \YapepBase\Request\HttpRequest   $request    The request object. Must be a HttpRequest or descendant.
     * @param \YapepBase\Response\HttpResponse $response   The response object. Must be a HttpResponse or descendant.
     *
     * @throws \YapepBase\Exception\ControllerException   On error. (eg. incompatible request or response object)
     */
    public function __construct(IRequest $request, IResponse $response) {
        if (!($request instanceof HttpRequest)) {
            throw new ControllerException('The specified request is not a HttpRequest',
                ControllerException::ERR_INCOMPATIBLE_REQUEST);
        }
        if (!($response instanceof HttpResponse)) {
            throw new ControllerException('The specified response is not a HttpResponse',
                ControllerException::ERR_INCOMPATIBLE_RESPONSE);
        }
        parent::__construct($request, $response);
    }

    /**
     * Redirects the client to the specified URL.
     *
     * @param string $url          The URL to redirect to.
     * @param int    $statusCode   The status code of the redirect (3XX).
     *
     * @throws \YapepBase\Exception\RedirectException   To stop execution of the controller.
     */
    protected function redirectToUrl($url, $statusCode = 303) {
        $this->response->redirect($url, $statusCode);
    // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    /**
     * Redirects the client to the URL specified by the controller and action.
     *
     * @param string $controller    The name of the controller.
     * @param string $action        The action of the controller.
     * @param array  $routeParams   Associative array containing the route parameters for the URL.
     * @param array  $getParams     Associative array containing the GET parameters for the URL.
     * @param string $anchor        The anchor for the URL
     * @param int    $statusCode    The status code of the redirect (3XX).
     *
     * @throws \YapepBase\Exception\RedirectException   To stop execution of the controller.
     * @throws \YapepBase\Exception\RouterException     If there was an error creating the URL.
     */
    protected function redirectToRoute(
        $controller, $action, $routeParams = array(), $getParams = array(), $anchor = '', $statusCode = 303
    ) {
        $url = Application::getInstance()->getRouter()->getTargetForControllerAction($controller, $action,
            $routeParams);
        if (!empty($getParams)) {
            $url .= '?' . \http_build_query($getParams, null, '&');
        }
        if (!empty($anchor)) {
            $url .= '#' . $anchor;
        }
        $this->redirectToUrl($url, $statusCode);
    // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd
}