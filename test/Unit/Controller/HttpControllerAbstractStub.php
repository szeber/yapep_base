<?php
declare(strict_types=1);

namespace YapepBase\Test\Unit\Controller;

use YapepBase\Controller\HttpControllerAbstract;

class HttpControllerAbstractStub extends HttpControllerAbstract
{
    public function redirectToUrl(string $url, int $statusCode = 303): void
    {
        parent::redirectToUrl($url, $statusCode);
    }

    public function redirectToRoute(
        string $controller,
        string $action,
        array $routeParams = [],
        array $getParams = [],
        string $anchor = '',
        int $statusCode = 303
    ): void {
        parent::redirectToRoute($controller, $action, $routeParams, $getParams, $anchor, $statusCode);
    }
}
