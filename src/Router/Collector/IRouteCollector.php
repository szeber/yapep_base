<?php
declare(strict_types=1);

namespace YapepBase\Router\Collector;

use YapepBase\Router\Entity\Route;

interface IRouteCollector
{
    /**
     * @return Route[]
     */
    public function getCollectedRoutes(): array;
}
