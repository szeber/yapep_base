<?php
declare(strict_types = 1);

namespace YapepBase\Router\Caching;

use YapepBase\Router\DataObject\Route;

interface IRouteCollector
{

    /**
     * @return Route[]
     */
    public function getCollectedRoutes(): array;
}
