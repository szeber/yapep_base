<?php

namespace YapepBase\Test\Mock\Router;

/**
 * @codeCoverageIgnore
 */
class ApplicationRouterMock implements \YapepBase\Router\IRouter {
    public function getRoute(&$controller = null, &$action = null) {
        $controller = 'ApplicationMock';
        $action = 'test';
        return $controller . '/' . $action;
    }
    public function getTargetForControllerAction($controller, $action, $params = array()) {
        return '/';
    }
}